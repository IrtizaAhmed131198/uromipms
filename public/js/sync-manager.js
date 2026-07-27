// sync-manager.js - Complete offline sync solution
class SyncManager {
    constructor() {
        this.dbPromise = null;
        this.syncing = false;
        this.init();
    }

    async init() {
        // Wait for dbPromise to be available
        let retries = 0;
        while (typeof dbPromise === 'undefined' && retries < 50) {
            await new Promise(resolve => setTimeout(resolve, 100));
            retries++;
        }

        if (typeof dbPromise === 'undefined') {
            console.error('❌ Failed to initialize SyncManager: dbPromise not available');
            return;
        }

        this.dbPromise = dbPromise;
        console.log('✅ SyncManager initialized');

        // Check for pending syncs on load
        this.checkPendingSync();
    }

    async checkPendingSync() {
        try {
            const queueSize = await this.getQueueSize();
            if (queueSize > 0) {
                console.log(`📋 Found ${queueSize} pending requests`);
                if (navigator.onLine) {
                    console.log('🔄 Online - starting auto-sync');
                    this.syncAll();
                } else {
                    console.log('🔴 Offline - will sync when connection restored');
                }
            }
        } catch (error) {
            console.error('Error checking pending sync:', error);
        }
    }

    async queueRequest(url, method, body, additionalData = {}) {
        try {
            const db = await this.dbPromise;
            const id = await db.add('sync-queue', {
                url,
                method,
                body,
                timestamp: Date.now(),
                ...additionalData
            });
            console.log('✅ Queued request:', {
                id,
                url,
                method
            });

            // Show notification
            if (typeof toastr !== 'undefined') {
                toastr.info('Request saved offline. Will sync when online.', 'Queued', {
                    timeOut: 3000
                });
            }

            return id;
        } catch (error) {
            console.error('❌ Error queueing request:', error);
            throw error;
        }
    }

    async syncAll() {
        if (this.syncing) {
            console.log('⏳ Sync already in progress');
            return;
        }

        if (!navigator.onLine) {
            console.log('🔴 Still offline, skipping sync');
            return;
        }

        this.syncing = true;
        console.log('🔄 Starting sync...');

        try {
            const db = await this.dbPromise;
            const queue = await db.getAll('sync-queue');

            if (queue.length === 0) {
                console.log('✅ No requests to sync');
                this.syncing = false;
                return;
            }

            console.log(`📤 Syncing ${queue.length} queued requests...`);

            let successCount = 0;
            let failCount = 0;

            for (const item of queue) {
                try {
                    console.log(`🔄 Syncing: ${item.method} ${item.url}`);

                    const response = await fetch(item.url, {
                        method: item.method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(item.body)
                    });

                    if (response.ok) {
                        await db.delete('sync-queue', item.id);
                        successCount++;
                        console.log(`✅ Synced: ${item.url}`);
                    } else {
                        failCount++;
                        console.error(`❌ Sync failed (HTTP ${response.status}):`, item.url);
                    }
                } catch (err) {
                    failCount++;
                    console.error(`❌ Sync failed for ${item.url}:`, err);
                }
            }

            console.log(`✅ Sync complete: ${successCount} success, ${failCount} failed`);

            // Show notification
            if (typeof toastr !== 'undefined') {
                if (successCount > 0) {
                    toastr.success(`Synced ${successCount} offline requests`, 'Sync Complete', {
                        timeOut: 5000
                    });
                }
                if (failCount > 0) {
                    toastr.error(`${failCount} requests failed to sync`, 'Sync Error', {
                        timeOut: 5000
                    });
                }
            }

        } catch (error) {
            console.error('❌ Error during sync:', error);
            if (typeof toastr !== 'undefined') {
                toastr.error('Sync failed', 'Error');
            }
        } finally {
            this.syncing = false;
        }
    }

    async getQueueSize() {
        try {
            const db = await this.dbPromise;
            const queue = await db.getAll('sync-queue');
            return queue.length;
        } catch (error) {
            console.error('Error getting queue size:', error);
            return 0;
        }
    }

    async getQueue() {
        try {
            const db = await this.dbPromise;
            return await db.getAll('sync-queue');
        } catch (error) {
            console.error('Error getting queue:', error);
            return [];
        }
    }

    async clearQueue() {
        try {
            const db = await this.dbPromise;
            const tx = db.transaction('sync-queue', 'readwrite');
            await tx.objectStore('sync-queue').clear();
            await tx.done;
            console.log('✅ Queue cleared');
        } catch (error) {
            console.error('Error clearing queue:', error);
        }
    }

    async deleteQueueItem(id) {
        try {
            const db = await this.dbPromise;
            await db.delete('sync-queue', id);
            console.log(`✅ Deleted queue item ${id}`);
        } catch (error) {
            console.error('Error deleting queue item:', error);
        }
    }
}

// Create global instance
const syncManager = new SyncManager();

// Auto-sync when coming back online
window.addEventListener('online', () => {
    console.log('🟢 Back online - syncing queued requests');
    syncManager.syncAll();

    // Show notification
    if (typeof toastr !== 'undefined') {
        toastr.success('You are back online! Syncing...', 'Connected', {
            timeOut: 3000
        });
    }
});

window.addEventListener('offline', () => {
    console.log('🔴 You are now offline');

    // Show notification
  
});

// Expose for debugging
window.syncManager = syncManager;