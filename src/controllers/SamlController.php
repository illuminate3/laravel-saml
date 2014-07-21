<?php

use KnightSwarm\LaravelSaml\Account;
use \Saml;
use \Auth;
use \Session;

class SamlController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Saml Controller
	|--------------------------------------------------------------------------
	|
	| This Controller should handle users and auth through SAML
	|
	*/

    private $act;

    public function __construct(KnightSwarm\LaravelSaml\Account $act)
    {
        $this->account = $act;
    }

	public function login()
	{

    	if (!$this->account->samlLogged()) {
            Auth::logout();
            $this->account->samlLogin();
        }

        if ($this->account->samlLogged()) {
            $uid = $this->account->getSamlUid();
            if (!$this->account->UIDExists($uid)) {
                $this->account->createUser();
            } else {
                if (!$this->account->laravelLogged()) {
                    $this->account->laravelLogin($uid);
                }
            }
        }

        if ($this->account->samlLogged() && $this->account->laravelLogged()) {
            $intended = Session::get('url.intended');
            $intended = str_replace(Config::get('app.url'), '', $intended);
            Session::flash('url.intended', $intended);
            return Redirect::intended('/');
        }

	}

    public function logout()
    {
        $auth_cookie = $this->account->logout();
		return Redirect::to(Config::get('laravel-saml::saml.logout_target', 'http://'.$_SERVER['SERVER_NAME']))->withCookie($auth_cookie);
    }

}
