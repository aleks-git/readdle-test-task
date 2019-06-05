
<header>
    <nav class="navbar navbar-expand-md navbar-dark bg-dark">
        <div class="container">
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item active">
                        <?php
                            if ($users->isAuth()) echo '<a href="?exit=1" class="nav-link">Logout</a>';
                            else echo '<a href="\" class="nav-link">Login</a>';
                        ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>