<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - <?php echo SITE_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                        secondary: '#1e40af',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="text-center">
        <div class="mb-8">
            <i class="fas fa-exclamation-triangle text-6xl text-gray-400 mb-4"></i>
            <h1 class="text-6xl font-bold text-gray-800 mb-4">404</h1>
            <h2 class="text-2xl font-semibold text-gray-600 mb-4">Page Not Found</h2>
            <p class="text-gray-500 mb-8">The page you're looking for doesn't exist or has been moved.</p>
        </div>
        
        <div class="space-x-4">
            <a href="/" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-secondary transition-colors inline-flex items-center">
                <i class="fas fa-home mr-2"></i>
                Go Home
            </a>
            <a href="javascript:history.back()" class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-colors inline-flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Go Back
            </a>
        </div>
    </div>
</body>
</html>
