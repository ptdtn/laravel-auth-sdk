<?php

namespace PTDTN\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard as AuthGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class Guard implements AuthGuard {
    /**
     * The currently authenticated user.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    protected $user;

    /**
     * The user provider implementation.
     *
     * @var \PTDTN\Auth\UserProvider
     */
    protected UserProvider $provider;
    /**
     * Indicates if the logout method has been called.
     *
     * @var bool
     */
    protected $loggedOut = false;
    /**
     * Configuration index.
     *
     * @var int
     */
    protected $clientIndex = 0;

    protected Request $request;
    protected string $refreshTokenName;
    protected string $tokenName;
    protected string $scopes;
    protected array $config;

    function __construct(UserProvider $provider, Request $request, $config = [], $clientIndex) {
        $this->request = $request;
        $this->provider = $provider;
        $this->refreshTokenName = 'refresh_token';
        $this->tokenName = 'token';
        $this->scopes = PTDTNToken::$scope;
        $this->config = $config;
        $this->user = NULL;
        $this->clientIndex = $clientIndex;
    }

    private function getClientConfig($configName) {
        return $this->config['clients'][$this->clientIndex][$configName];
    }

    private function getRedirectUri() {
        return $this->getClientConfig('redirect_base_url') . $this->getClientConfig('redirect_url');
    }

    private function jsonRequest() {
        return Http::asForm()
            ->withHeaders([
                'Accept' => 'application/json',
            ]);
    }

    private function authorizedRequest(string $token) {
        return $this->jsonRequest()
            ->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ]);
    }

    function check(): bool {
        return null !== $this->user();
    }

    function guest(): bool {
        return !$this->check();
    }

    private function setSessions(array $data) {
        $this->loggedOut = false;
        $this->request->session()->put($this->tokenName, $data['access_token']);
        $this->request->session()->put($this->refreshTokenName, $data['refresh_token']);
    }

    private function clearSessions() {
        $this->request->session()->pull($this->tokenName);
        $this->request->session()->pull($this->refreshTokenName);
    }

    function refreshToken() {
        $token = $this->request->session()->get($this->refreshTokenName);
        if (empty($token)) return false;

        $response = $this->jsonRequest()->post($this->config['base_url'] . '/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $token,
            'client_id' => $this->getClientConfig('client_id'),
            'client_secret' => $this->getClientConfig('secret'),
            'scope' => $this->scopes,
        ]);

        $data = $response->json();
        if ($response->status() == 200 && empty($data['error'])) {
            $this->setSessions($data);
            return true;
        } else {
            Log::error($data);
        }
        return false;
    }

    public function user() {
        if ($this->loggedOut) {
            return;
        }
        if (!empty($this->user)) return $this->user;
        if (!$this->request instanceof \Illuminate\Http\Request) {
            return;
        }

        $token = $this->request->session()->get($this->tokenName);
        $token = $this->request->header('Authorization');
        if (empty($token)) {
            return;
        }
        if (str_starts_with($token, 'Bearer')) {
            $token = substr($token, 7);
        }

        $response = $this->authorizedRequest($token)->get($this->config['base_url'] . '/api/me');

        if ($response->status() == 200) {
            $pTDTNUser = User::createFromJson($response->json());
            $user = $this->provider->retrieveById($pTDTNUser->id);
            if (empty($user)) {
                $user = $this->provider->signUp($pTDTNUser);
            }
            $user->ptdtnUser = $pTDTNUser;
            $this->setUser($user);
            return $this->user;
        } else {
            // token expired
            $this->request->session()->pull($this->tokenName);
            if ($this->refreshToken()) {
                return $this->user();
            }
        }
        return;
    }

    function id() {
        if ($this->loggedOut) {
            return;
        }
        if ($this->user()) {
            return $this->user()->getAuthIdentifier();
        }
    }

    function setUser(?Authenticatable $user) {
        $this->user = $user;
        if (empty($user)) {
            $this->loggedOut = true;
        } else {
            $this->loggedOut = false;
        }
        return $this;
    }

    function validate(array $credentials = []) {
        return false;
    }

    function signOut() {
        if (!$this->request instanceof \Illuminate\Http\Request) {
            return false;
        }

        $token = $this->request->session()->pull($this->tokenName);
        if (empty($token)) {
            return false;
        }

        $response = $this->authorizedRequest($token)->get($this->config['base_url'] . '/api/sign-out');

        if ($response->status() == 200) {
            $this->clearSessions();
            $this->user = null;

            $this->loggedOut = true;
            return true;
        } else {
            Log::error($response->json());
        }
        return false;
    }

    function getAuthorizationUrl(string $state) {
        $query = http_build_query([
            'client_id' => $this->getClientConfig('client_id'),
            'redirect_uri' => $this->getRedirectUri(),
            'response_type' => 'code',
            'scope' => $this->scopes,
            'state' => $state,
        ]);
        return $this->config['base_url'] . '/signin?' . $query;
    }

    function signIn($authCode) {
        if (empty($authCode)) return false;

        $response = Http::asForm()->post($this->config['base_url'] . '/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $this->getClientConfig('client_id'),
            'client_secret' => $this->getClientConfig('secret'),
            'redirect_uri' => $this->getRedirectUri(),
            'code' => $authCode,
        ]);

        $data = $response->json();
        if ($response->status() == 200 && empty($data['error'])) {
            $this->setSessions($data);
            return $data;
        } else {
            Log::error($data);
        }
        return false;
    }
}
