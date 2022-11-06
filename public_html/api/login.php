<?php

use JetBrains\PhpStorm\NoReturn;

class User
{

    public int $id;
    public string $first_name;
    public string $last_name;
    public string $email;
    public string $password_hash;
    public string $salt;

    /**
     * @param int $id
     * @param string $first_name
     * @param string $last_name
     * @param string $email
     * @param string $password_hash
     * @param string $salt
     */
    public function __construct(int $id, string $first_name, string $last_name, string $email, string $password_hash, string $salt)
    {
        $this->id = $id;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->email = $email;
        $this->password_hash = $password_hash;
        $this->salt = $salt;
    }
}

class Logger {
    private string $log_file;

    /**
     * @param string $log_file
     */
    public function __construct(string $log_file)
    {
        if(!file_exists(__DIR__ . '/../var/'))
            mkdir(__DIR__ . '/../var/');
        $this->log_file = __DIR__ . '/../var/' . $log_file;
    }


    public function Log(string $message, mixed ...$values): void
    {

        $message = $message . PHP_EOL;
        file_put_contents($this->log_file, vsprintf($message, $values), FILE_APPEND);
    }

}

function validate_email(string $email): bool
{
    return str_contains($email, '@');
    //    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_passwords($password1, $password2): bool
{
    return $password1 == $password2;
}

function is_user_exist($email): bool
{
    global $created_users;
    foreach ($created_users as $user) {
        if ($user->email === $email)
            return true;
    }
    return false;
}

function create_user($first_name,
                     $last_name,
                     $email,
                     $password): bool
{
    global $logger;
    global $created_users;
    $id = count($created_users);

    $salt = random_bytes(SODIUM_CRYPTO_PWHASH_SALTBYTES);

    $password_hash = sodium_crypto_pwhash(
        16,
        $password,
        $salt,
        SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
        SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE);

    $created_users[$id] = new User($id, $first_name, $last_name, $email, $password_hash, $salt);
    $logger->Log('Created user %s, %s - %s:%s', $first_name, $last_name, $email, $password);
    return true;
}

#[NoReturn] function exit_error($msg)
{
    $status['status'] = 'error';
    $status['message'] = $msg;
    echo json_encode($status);
    die;
}

#[NoReturn] function exit_success($msg)
{
    $status['status'] = 'success';
    $status['message'] = $msg;
    echo json_encode($status);
    die;
}

$logger = new Logger('log.log');
$created_users = [];

create_user("Иван", "Иванов", "test@mail.ru", 'qwerty');
create_user("Петр", "Петров", "example@mail.ru", 'q1w2e3');
create_user("Василий", "Васильев", "vasiliy@mail.ru", 'vasiliy');
create_user("Платон", "Сакратов", "platon@mail.gr", 'greek_is_da_best');

$status = [];


$email = $_POST['email'];
if ($email === "") {
    exit_error('E-mail не может быть пустым');
}
if (!validate_email($email)) {
    exit_error('Некорректный e-mail адрес');
}


$password1 = $_POST['password1'];
$password2 = $_POST['password2'];
if ($password1 === "" || $password2 === "") {
    exit_error('Пароль не может быть пустым');
}
if (!validate_passwords($password1, $password2)) {
    exit_error('Пароли не совпадают');
}


$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];

if ($first_name === ""){
    exit_error('Имя не может быть пустым');
}
if ($last_name === ""){
    exit_error('Фамилия не может быть пустой');
}

if (is_user_exist($email)) {
    $logger->Log('Trying to create user for busy email %s, %s - %s:%s', $first_name, $last_name, $email, $password1);
    exit_error('Этот email уже зарегестрирован');
}

create_user($_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['password1']);
exit_success('Пользователь успешно зарегестрирован.');
