<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            <?php echo e(__('API Dashboard')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            
            <?php if(session('success')): ?>
                <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><?php echo e(session('success')); ?></span>
                </div>
            <?php endif; ?>

            
            <?php if(session('plain_key')): ?>
                <div class="bg-yellow-100 dark:bg-yellow-900 border border-yellow-400 dark:border-yellow-700 text-yellow-700 dark:text-yellow-300 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Important!</strong>
                    <span class="block sm:inline">Copy this API key now. It will not be shown again.</span>
                    <div class="mt-4 p-4 bg-gray-800 text-white rounded font-mono text-sm break-all flex justify-between items-center">
                        <span id="apiKeyValue"><?php echo e(session('plain_key')); ?></span>
                        <button onclick="copyApiKey()" class="ml-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Copy
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">API Key Management</h3>
                    
                    <?php if($apiKey): ?>
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                <strong>Status:</strong> 
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Active
                                </span>
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                <strong>Created:</strong> <?php echo e($apiKey->created_at->format('Y-m-d H:i:s')); ?>

                            </p>
                            <?php if($apiKey->last_used_at): ?>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                    <strong>Last Used:</strong> <?php echo e($apiKey->last_used_at->format('Y-m-d H:i:s')); ?>

                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="flex space-x-4">
                            <form method="POST" action="<?php echo e(route('admin.api-key.regenerate')); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded" onclick="return confirm('Are you sure? The current API key will be invalidated.')">
                                    Regenerate API Key
                                </button>
                            </form>

                            <form method="POST" action="<?php echo e(route('admin.api-key.disable')); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" onclick="return confirm('Are you sure? This will disable API access.')">
                                    Disable API Key
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">No active API key found.</p>
                        <form method="POST" action="<?php echo e(route('admin.api-key.generate')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Generate API Key
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">API Base URL</h3>
                    <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded font-mono text-sm break-all flex justify-between items-center">
                        <span id="baseUrl"><?php echo e($baseUrl); ?></span>
                        <button onclick="copyBaseUrl()" class="ml-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Copy
                        </button>
                    </div>
                </div>
            </div>

            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Available Tables & Endpoints</h3>
                    
                    <?php if(count($tables) > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Table Name
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Endpoint
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Description
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <?php $__currentLoopData = $tables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <?php echo e($table['name']); ?>

                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 dark:text-blue-400 font-mono">
                                                GET <?php echo e($baseUrl); ?><?php echo e($table['endpoint']); ?>

                                            </td>
                                            <td class="px-6 py-4 text-sm">
                                                <?php echo e($table['description']); ?>

                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <?php echo e($table['name']); ?>

                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 dark:text-blue-400 font-mono">
                                                GET <?php echo e($baseUrl); ?><?php echo e($table['endpoint']); ?>/{id}
                                            </td>
                                            <td class="px-6 py-4 text-sm">
                                                Get single record by ID
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-gray-600 dark:text-gray-400">No tables available.</p>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">API Usage</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <h4 class="font-semibold mb-2">Authentication</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Include your API key in the request header:</p>
                            <pre class="p-4 bg-gray-100 dark:bg-gray-700 rounded text-sm overflow-x-auto"><code>X-API-Key: your_api_key_here</code></pre>
                        </div>

                        <div>
                            <h4 class="font-semibold mb-2">Example Request</h4>
                            <pre class="p-4 bg-gray-100 dark:bg-gray-700 rounded text-sm overflow-x-auto"><code>curl -X GET "<?php echo e($baseUrl); ?>/clients?page=1&pageSize=50" \
  -H "X-API-Key: your_api_key_here"</code></pre>
                        </div>

                        <div>
                            <h4 class="font-semibold mb-2">Available Query Parameters</h4>
                            <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                <li><code>clientNum</code> - Filter by client number (exact match)</li>
                                <li><code>payroll</code> - Filter by payroll (exact match)</li>
                                <li><code>active</code> - Filter by active status (true/false)</li>
                                <li><code>nameContains</code> - Search in name field</li>
                                <li><code>clientNameContains</code> - Search in client name field</li>
                                <li><code>dateAddedFrom</code> - Filter by date added (from)</li>
                                <li><code>dateAddedTo</code> - Filter by date added (to)</li>
                                <li><code>sort</code> - Sort results (clientNum, dateAdded, lastTDate, prefix with - for DESC)</li>
                                <li><code>page</code> - Page number (default: 1)</li>
                                <li><code>pageSize</code> - Results per page (default: 50, max: 500)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function copyApiKey() {
            const apiKey = document.getElementById('apiKeyValue').textContent;
            navigator.clipboard.writeText(apiKey).then(() => {
                alert('API key copied to clipboard!');
            });
        }

        function copyBaseUrl() {
            const baseUrl = document.getElementById('baseUrl').textContent;
            navigator.clipboard.writeText(baseUrl).then(() => {
                alert('Base URL copied to clipboard!');
            });
        }
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\MicroPOS\TestingAPI\mssql-api-dashboard\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>