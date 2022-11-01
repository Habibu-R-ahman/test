$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (isset($data['action']) && ($data['action'] == "password_reset")) {
    $user = $data['user'];
    $reset_token = $data['reset_token'];

    $valid_token = get_reset_token($user); // returns MD5 hash from DB

    if (is_null($valid_token)) { // no reset token in DB
        header('Location: /');
        die();
    }

    if ($reset_token == $valid_token) { // supplied reset token matches with the one in DB
        $_SESSION['user_logged_in'] = True;
        $_SESSION['user'] = $user;
        header('Location: /user');
        die();
    }
}
