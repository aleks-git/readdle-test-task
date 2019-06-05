<main class="container jumbotron bg-white">
    <div class="container text-center">
        <form class="form-signin w-25 m-auto" method="post">
            <h1 class="h3 mb-3 font-weight-normal">Please sign in</h1>
            <div class="mt-2">
                <label for="inputEmail" class="sr-only">Login</label>
                <input type="text" name="login" id="inputEmail" class="form-control" placeholder="Login" required autofocus>
            </div>
            <div class="mt-2">
                <label for="inputPassword" class="sr-only">Password</label>
                <input type="password" name="pass" id="inputPassword" class="form-control" placeholder="Password" required>
            </div>

            <button class="btn btn-lg btn-primary btn-block mt-4" type="submit">Sign in</button>
        </form>

        <?php if(isset($_GET['error']) && $_GET['error']) echo '<div class="m-3">
                      <div class="m-auto p-2 alert-danger w-25 ">Wrong login or password!</div></div>' ?>

        <div class="mt-4">
            <p>Or you can watch only preview email <a href="simple_list.php" class="font-weight-bold">here</a>.</p>
        </div>
    </div>
</main>