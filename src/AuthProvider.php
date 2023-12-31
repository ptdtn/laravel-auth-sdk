<?php

namespace PTDTN\Auth;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AuthProvider extends ServiceProvider {
	const ConfigName = 'ptdtntoken';

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register() {
		$this->mergeConfigFrom($this->configPath(), self::ConfigName);

		Auth::resolved(function ($auth) {
			$auth->extend('ptdtntoken', function ($app, $name) {
				$config = $this->app['config']->get(self::ConfigName);
				$guard = empty($name) ? $this->app['config']->get('auth.defaults.guard') : $name;
				$provider = $this->app['config']->get('auth.guards.'.$guard.'.provider');
				$model = $this->app['config']->get('auth.providers.'.$provider.'.model');
				$clientIndex = $this->app['config']->get('auth.guards.'.$guard.'.client_index') ?? 0;
				return new Guard(new UserProvider($model), $app->make('request'), $config, $clientIndex);
			});
		});
	}

	/**
	 * Register the config for publishing
	 *
	 */
	public function boot() {
		if ($this->app->runningInConsole()) {
			$this->publishes([$this->configPath() => config_path(self::ConfigName . '.php')], self::ConfigName);
			$this->publishes([
				__DIR__ . '/../database/migrations' => database_path('migrations'),
			], self::ConfigName);
		}
	}

	/**
	 * Set the config path
	 *
	 * @return string
	 */
	protected function configPath() {
		return __DIR__ . '/../config/' . self::ConfigName . '.php';
	}
}
