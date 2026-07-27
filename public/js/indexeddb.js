const dbPromise = idb.openDB('category_db', 1, {
    upgrade(db) {
        if (!db.objectStoreNames.contains('categories')) {
            const store = db.createObjectStore('categories', {
                keyPath: 'id'
            });
            store.createIndex('parent_id', 'parent_id');
        }
    }
});
