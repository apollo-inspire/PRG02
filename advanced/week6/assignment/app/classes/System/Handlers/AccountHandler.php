<?php namespace System\Handlers;

use System\Databases\Database;
use System\Form\Data;
use System\Form\Validation\LoginValidator;
use System\Users\User;

/**
 * Class AccountHandler
 * @package System\Handlers
 */
class AccountHandler extends BaseHandler
{
    protected function login()
    {
        //If already logged in, no need to be here
        if ($this->session->keyExists('user')) {
            header('Location: add');
            exit;
        }

        //Check if Post isset, else do nothing
        if (isset($_POST['submit'])) {
            //Set form data
            $formData = new Data($_POST);

            //Set post variables
            $email = $formData->getPostVar('email');
            $password = $formData->getPostVar('password');

            //Init the database
            $db = (new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME))->getConnection();

            //Get the record from the db
            try {
                $user = User::getByEmail($email, $db);
            } catch (\Exception $e) {
                //Probably should work nicer
                $user = new User();
            }

            //Actual validation
            $validator = new LoginValidator($user, $password);
            $validator->validate();
            $this->errors = $validator->getErrors();
        }

        //When no error, set session variable, redirect & exit script
        if (isset($formData) && empty($this->errors)) {
            $this->session->set('user', $user);
            header('Location: add');
            exit;
        }

        //Return formatted data
        $this->renderTemplate([
            'pageTitle' => 'Login',
            'email' => $email ?? false,
            'errors' => $this->errors
        ]);
    }

    protected function logout()
    {
        $this->session->destroy();
        header("Location: login");
        exit;
    }

    protected function user()
    {
        //TEMP script just to add an user.
        $db = (new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME))->getConnection();
        $user = new User();
        $user->email = 'moora@hr.nl';
        $user->password = password_hash('test', PASSWORD_ARGON2I);
        $user->name = 'Antwan';
        User::add($user, $db);
        exit;
    }
}
