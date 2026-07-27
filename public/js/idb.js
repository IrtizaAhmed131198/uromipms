// idb.js - IndexedDB initialization and helpers
let dbPromise;

// Initialize IndexedDB
async function initDB() {
    try {
        dbPromise = idb.openDB('pos-db', 3, {
            upgrade(db, oldVersion, newVersion, transaction) {
                console.log('Upgrading DB from version', oldVersion, 'to', newVersion);

                // Categories store
                if (!db.objectStoreNames.contains('categories')) {
                    const categoryStore = db.createObjectStore('categories', {
                        keyPath: 'id'
                    });
                    categoryStore.createIndex('parent_id', 'parent_id', { unique: false });
                    console.log('Created categories store');
                } else if (oldVersion < 3) {
                    const store = transaction.objectStore('categories');
                    if (!store.indexNames.contains('parent_id')) {
                        store.createIndex('parent_id', 'parent_id', { unique: false });
                        console.log('Added parent_id index to categories');
                    }
                }

                // Products store
                if (!db.objectStoreNames.contains('products')) {
                    db.createObjectStore('products', { keyPath: 'id' });
                    console.log('Created products store');
                }

                // Sync queue store
                if (!db.objectStoreNames.contains('sync-queue')) {
                    db.createObjectStore('sync-queue', {
                        keyPath: 'id',
                        autoIncrement: true
                    });
                    console.log('Created sync-queue store');
                }
            }
        });

        console.log('IndexedDB initialized successfully');
        return await dbPromise;
    } catch (error) {
        console.error('Failed to initialize IndexedDB:', error);
        throw error;
    }
}

// Initialize on load
initDB().catch(err => {
    console.error('Critical: IndexedDB initialization failed', err);
});

// Helper: Save categories to IndexedDB
async function saveCategories(categories, parentId = null) {
    try {
        const db = await dbPromise;
        const tx = db.transaction('categories', 'readwrite');
        const store = tx.objectStore('categories');

        for (const cat of categories) {
            await store.put({
                id: cat.id,
                name: cat.name,
                parent_id: parentId ? Number(parentId) : null
            });
        }

        await tx.done;
        console.log(`Saved ${categories.length} categories with parent_id: ${parentId}`);
    } catch (error) {
        console.error('Error saving categories:', error);
    }
}

// Helper: Get subcategories by parent ID
async function getSubCategories(parentId) {
    try {
        const db = await dbPromise;
        const tx = db.transaction('categories', 'readonly');
        const index = tx.objectStore('categories').index('parent_id');
        const categories = await index.getAll(Number(parentId));
        await tx.done;
        
        console.log(`Retrieved ${categories.length} subcategories for parent: ${parentId}`);
        return categories;
    } catch (error) {
        console.error('Error getting subcategories:', error);
        return [];
    }
}

// Helper: Clear all categories (useful for refresh)
async function clearCategories() {
    try {
        const db = await dbPromise;
        const tx = db.transaction('categories', 'readwrite');
        await tx.objectStore('categories').clear();
        await tx.done;
        console.log('Cleared all categories');
    } catch (error) {
        console.error('Error clearing categories:', error);
    }
}