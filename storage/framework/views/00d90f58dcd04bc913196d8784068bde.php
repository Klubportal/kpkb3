<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neuen Tenant erstellen - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <h1 class="text-2xl font-bold text-gray-900">➕ Neuen Tenant erstellen</h1>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <?php if($errors->any()): ?>
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                <div class="font-bold mb-2">❌ Fehler beim Erstellen:</div>
                <ul class="list-disc list-inside space-y-1">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
            <?php endif; ?>

            <form action="<?php echo e(route('admin.tenants.store')); ?>" method="POST" class="space-y-6">
                <?php echo csrf_field(); ?>

                <!-- Basis-Informationen -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">📋 Basis-Informationen</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Vereinsname <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="name"
                                   value="<?php echo e(old('name')); ?>"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="NK Prigorje Markovec">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Subdomain <span class="text-red-500">*</span>
                            </label>
                            <div class="flex">
                                <input type="text"
                                       name="subdomain"
                                       value="<?php echo e(old('subdomain')); ?>"
                                       required
                                       pattern="[a-z0-9-]+"
                                       class="flex-1 px-4 py-2 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="nkprigorjem">
                                <span class="px-4 py-2 bg-gray-100 border border-l-0 border-gray-300 rounded-r-lg text-gray-600">
                                    .localhost
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Nur Kleinbuchstaben, Zahlen und Bindestriche</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                E-Mail <span class="text-red-500">*</span>
                            </label>
                            <input type="email"
                                   name="email"
                                   value="<?php echo e(old('email')); ?>"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="info@prigorjem.hr">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Telefon
                            </label>
                            <input type="text"
                                   name="phone"
                                   value="<?php echo e(old('phone')); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="+385 1 234 5678">
                        </div>
                    </div>
                </div>

                <!-- FIFA/COMET Daten -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">🏆 FIFA/COMET Daten</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Club FIFA ID <span class="text-red-500">*</span>
                            </label>
                            <input type="number"
                                   name="club_fifa_id"
                                   value="<?php echo e(old('club_fifa_id')); ?>"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="598">
                            <p class="mt-1 text-xs text-gray-500">Eindeutige FIFA ID des Clubs</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Organisation FIFA ID <span class="text-red-500">*</span>
                            </label>
                            <input type="number"
                                   name="organisation_fifa_id"
                                   value="<?php echo e(old('organisation_fifa_id')); ?>"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="1">
                            <p class="mt-1 text-xs text-gray-500">FIFA ID der Organisation (Verband)</p>
                        </div>
                    </div>
                </div>

                <!-- Club Details -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">📍 Club Details</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Ländercode (ISO 3166-1 alpha-3) <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="country_code"
                                   value="<?php echo e(old('country_code')); ?>"
                                   required
                                   maxlength="3"
                                   pattern="[A-Z]{3}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="HRV">
                            <p class="mt-1 text-xs text-gray-500">z.B. HRV, GER, AUT (Großbuchstaben)</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Stadt
                            </label>
                            <input type="text"
                                   name="city"
                                   value="<?php echo e(old('city')); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Zagreb">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Gründungsjahr
                            </label>
                            <input type="number"
                                   name="founded_year"
                                   value="<?php echo e(old('founded_year')); ?>"
                                   min="1800"
                                   max="<?php echo e(date('Y')); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="1945">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Website
                            </label>
                            <input type="url"
                                   name="website"
                                   value="<?php echo e(old('website')); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="https://prigorjem.hr">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Logo URL
                            </label>
                            <input type="url"
                                   name="logo_url"
                                   value="<?php echo e(old('logo_url')); ?>"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="https://example.com/logo.png">
                        </div>
                    </div>
                </div>

                <!-- Admin User -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">👤 Admin Benutzer</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Admin Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="admin_name"
                                   value="<?php echo e(old('admin_name')); ?>"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Max Mustermann">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Admin E-Mail <span class="text-red-500">*</span>
                            </label>
                            <input type="email"
                                   name="admin_email"
                                   value="<?php echo e(old('admin_email')); ?>"
                                   required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="admin@prigorjem.hr">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Admin Passwort <span class="text-red-500">*</span>
                            </label>
                            <input type="password"
                                   name="admin_password"
                                   required
                                   minlength="8"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Mindestens 8 Zeichen">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Passwort bestätigen <span class="text-red-500">*</span>
                            </label>
                            <input type="password"
                                   name="admin_password_confirmation"
                                   required
                                   minlength="8"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Passwort wiederholen">
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="text-2xl mr-3">ℹ️</div>
                        <div>
                            <h3 class="font-semibold text-blue-900 mb-2">Automatische COMET Synchronisation</h3>
                            <p class="text-sm text-blue-800">
                                Nach dem Erstellen des Tenants werden automatisch alle COMET-Daten
                                (Matches, Rankings, Top Scorers, etc.) aus der Central DB synchronisiert.
                                Dies kann einige Sekunden dauern.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-between items-center pt-4">
                    <a href="<?php echo e(route('admin.tenants.index')); ?>"
                       class="text-gray-600 hover:text-gray-900">
                        ← Zurück zur Übersicht
                    </a>

                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition">
                        ✅ Tenant erstellen
                    </button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\kpkb3\resources\views\admin\tenants\create-comet.blade.php ENDPATH**/ ?>