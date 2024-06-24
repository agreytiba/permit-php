<?php

function handleLogout()
{
    session_destroy();
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    handleLogout();
}

// Define navigation items
$navigationItems = [
    [
        'text' => 'Home',
        'path' => 'index.php',
        'icon' => '<i class="fas fa-home w-6 h-6"></i>'
    ],
    // [
    //     'text' => 'User Page',
    //     'path' => 'userpage.php',
    //     'icon' => '<i class="fas fa-user w-6 h-6"></i>'
    // ],

    [
        'text' => 'Requests',
        'children' => [
            [
                'text' => 'Request',
                'path' => 'request.php',
            ],
            [
                'text' => 'deleted request',
                'path' => 'request_deleted.php'
            ]
        ],
        'icon' => '<i class="fas fa-file-alt w-6 h-6"></i>'
    ],
    [
        'text' => 'Reviewed',
        'path' => 'review.php',
        'icon' => '<i class="fas fa-search w-6 h-6"></i>'
    ],
    [
        'text' => 'Approve',
        'path' => 'approve.php',
        'icon' => '<i class="fas fa-check w-6 h-6"></i>'
    ],
    [
        'text' => 'Declined',
        'path' => 'decline.php',
        'icon' => '<i class="fas fa-times w-6 h-6"></i>'
    ],
    [
        'text' => 'Successful',
        'path' => 'successful.php',
        'icon' => '<i class="fas fa-check-circle w-6 h-6"></i>'
    ],


    [
        'text' => 'users',
        'children' => [
            [
                'text' => 'users',
                'path' => 'user.php',
            ],
            [
                'text' => ' blocked users',
                'path' => 'blocked_user.php',
            ]
        ],
        'icon' => '<i class="fas fa-users w-6 h-6"></i>'
    ],
    [
        'text' => 'Admin users',
        'children' => [
            [
                'text' => 'Admins',
                'path' => 'admin_users.php',
            ],
            [
                'text' => 'Admins blocked',
                'path' => 'blocked_admin.php',
            ]
        ],
        'icon' => '<i class="fas fa-users w-6 h-6"></i>'
    ],
    [
        'text' => 'Reports',
        'path' => 'report.php',
        'icon' => '<i class="fas fa-chart-line w-6 h-6"></i>'
    ],
    // Add more items as needed
];

$currentPath = basename($_SERVER['PHP_SELF']);
?>

<div class="w-56 shadow-2xl border-2 m-2 rounded-lg p-4 fixed top-20 bottom-0 min-h-screen shadow-blue-500 brightness-100">
    <ul class="space-y-2">
        <?php foreach ($navigationItems as $item) : ?>
            <?php if (isset($item['children'])) : ?>
                <li class="relative">
                    <button class="w-full flex items-center justify-between px-2 py-2 text-left bg-gray-100 rounded-lg focus:outline-none dropdown-toggle">
                        <span class="flex items-center">
                            <?php echo $item['icon']; ?>
                            <span class="ml-3"><?php echo $item['text']; ?></span>
                        </span>
                        <i class="fas fa-chevron-down w-5 h-5"></i>
                    </button>
                    <ul class="mt-2 space-y-2 pl-5 hidden">
                        <?php foreach ($item['children'] as $child) : ?>
                            <li>
                                <a href="<?php echo $child['path']; ?>" class="block px-2 py-2 text-gray-700 rounded-lg hover:bg-gray-200 <?php echo ($currentPath === basename($child['path'])) ? 'bg-blue-200 text-blue-700' : ''; ?>">
                                    <?php echo $child['text']; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php else : ?>
                <li>
                    <a href="<?php echo $item['path']; ?>" class="block px-2 py-2 text-gray-700 rounded-lg hover:bg-gray-200 <?php echo ($currentPath === basename($item['path'])) ? 'bg-blue-200 text-blue-700' : ''; ?>">
                        <span class="flex items-center">
                            <?php echo $item['icon']; ?>
                            <span class="ml-3"><?php echo $item['text']; ?></span>
                        </span>
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>
</div>

<script>
    document.querySelectorAll('.dropdown-toggle').forEach(button => {
        button.addEventListener('click', () => {
            const dropdown = button.nextElementSibling;
            const isOpen = !dropdown.classList.contains('hidden');
            document.querySelectorAll('.dropdown-toggle').forEach(btn => {
                const dd = btn.nextElementSibling;
                if (dd !== dropdown) {
                    dd.classList.add('hidden');
                }
            });
            dropdown.classList.toggle('hidden', isOpen);
        });
    });
</script>