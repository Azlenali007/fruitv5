<!DOCTYPE html>
<html lang="en" class="h-full bg-[#f8faf8]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($installerTitle ?? 'Web Installer - N.A Fresh Fruits & Coconuts') ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#ebf5ed',
                            600: '#235d3a',
                            700: '#1b4a2e',
                        }
                    }
                }
            }
        }
    </script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="min-h-full flex flex-col justify-between text-gray-800 antialiased p-4 sm:p-6 lg:p-8">

<div class="max-w-2xl w-full mx-auto space-y-6">
    
    <!-- Installer Header Brand -->
    <div class="text-center space-y-2">
        <div class="inline-flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-2xl bg-brand-600 text-white flex items-center justify-center font-black text-xl shadow-xs">
                NA
            </div>
            <div class="text-left">
                <span class="font-extrabold text-xl tracking-tight text-gray-900 block leading-tight">N.A Fresh Fruits & Coconuts</span>
                <span class="text-xs text-gray-400 font-medium tracking-wide">Web Installation Wizard</span>
            </div>
        </div>
    </div>

    <!-- Wizard Stepper Indicator (6 Steps) -->
    <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-xs">
        <div class="flex items-center justify-between">
            <?php
            $steps = [
                1 => 'System',
                2 => 'Database',
                3 => 'Tables',
                4 => 'Admin',
                5 => 'Settings',
                6 => 'Finish'
            ];
            $currentStep = $currentStep ?? 1;
            foreach ($steps as $sNum => $sLabel):
                $isPassed = $sNum < $currentStep;
                $isCurrent = $sNum === $currentStep;
            ?>
            <div class="flex flex-col items-center flex-1">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition <?= $isPassed ? 'bg-brand-600 text-white' : ($isCurrent ? 'bg-brand-600 text-white ring-4 ring-emerald-100' : 'bg-gray-100 text-gray-400') ?>">
                    <?= $isPassed ? '✓' : $sNum ?>
                </div>
                <span class="text-[10px] mt-1 hidden sm:block font-semibold <?= $isCurrent ? 'text-brand-700' : ($isPassed ? 'text-gray-700' : 'text-gray-400') ?>">
                    <?= $sLabel ?>
                </span>
            </div>
            <?php if ($sNum < 6): ?>
            <div class="w-full flex-1 h-0.5 mx-1 <?= $sNum < $currentStep ? 'bg-brand-600' : 'bg-gray-200' ?>"></div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Main Step Card Container -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-gray-100 shadow-sm">
        <?= $stepContent ?? '' ?>
    </div>

</div>

<footer class="mt-8 text-center text-xs text-gray-400">
    &copy; <?= date('Y') ?> N.A Fresh Fruits & Coconuts &bull; Step-by-Step Production Web Installer
</footer>

</body>
</html>
