<?php

namespace PTDTN\Auth;

class UserProvider {

	/**
	 * The Eloquent user model.
	 *
	 * @var string
	 */
	protected $model;

	/**
	 * Create a new database user provider.
	 *
	 * @param  \Illuminate\Contracts\Hashing\Hasher  $hasher
	 * @param  string  $model
	 * @return void
	 */
	public function __construct($model) {
		$this->model = $model;
	}

	public function retrieveById($identifier) {
		$model = $this->createModel();

		return $this->newModelQuery($model)
			->where($model->getPTDTNAuthIdentifierName(), $identifier)
			->first();
	}

	public function signUp(User $user) {
		$model = $this->createModel();

		return $model->createFromPTDTNUser($user);
	}

	/**
	 * Create a new instance of the model.
	 *
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function createModel() {
		$class = '\\' . ltrim($this->model, '\\');

		return new $class;
	}

	/**
	 * Get a new query builder for the model instance.
	 *
	 * @param  \Illuminate\Database\Eloquent\Model|null  $model
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	protected function newModelQuery($model = null) {
		return is_null($model)
			? $this->createModel()->newQuery()
			: $model->newQuery();
	}
}
