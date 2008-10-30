<?php

require "../Config.php";

$config = new Config();
$config->absUri = dirname($config->absUri . '../');
$config->absUriPath = dirname($config->absUriPath . '../');

require "$config->absPath/lib/application/Application.php";

Application::requireMinimum();

$database = new Database();
$database->log = $database->time = $config->isDebugMode();

Main::startSession();

$config->info("==> ADMIN LOGIN REQUEST FROM " . Params::SERVER('REMOTE_ADDR') .' - ' . Params::SERVER('REQUEST_URI') . ' <==');

$err = "";
if (Params::post('name')) {
    $config->info("user name is " . Params::post('name'));
    
    if ($uid = User::login(Params::post('name'), Params::post('pass'))) {
        $config->debug("uid is $uid");
        
        $user = new User();
        if ($user->fetch($uid)) {        
            //if ($user->isAdministrator()) {
                $config->info("Admin #$user->id logged in");
                $_SESSION['user_id'] = $user->id;
                session_write_close();
                
                header('Location: index.php');
                exit(0);
            //} else {
            //  $err = "You are a user, but not an administrator";
            //}
        }
    } else {
        $err = "Unknown username & password";
    }
}

?>

<html>
    <head>
        <title>Login</title>
    </head>
    <body>
        <?php echo "<p>$err</p>"; ?>
    
        <form action = "login.php" method = "post">
            <table>
                <tr>
                    <th><label for = "name">Name</label></th>
                    <td><input type = "text" name = "name" id = "name" /></td>                    
                </tr>
                <tr>
                    <th><label for = "pass">Password</label></th>
                    <td><input type = "password" name = "pass" id = "pass" /></td>                    
                </tr>                
            </table>
            
            <button type = "submit">Login</button>
        </form>
    </body>
</html>