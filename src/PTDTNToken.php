<?php

namespace PTDTN\Auth;

class PTDTNToken {
	/**
	 * The scope.
	 *
	 * @var string
	 */
	public static $scope = 'user-info';

	/**
	 * Set the scope(s). Multiple scopes may be an array or specified delimited by spaces.
	 *
	 * @param  array|string  $scope
	 * @return void
	 */
	public static function setScope($scope) {
		static::$scope = is_array($scope) ? implode(' ', $scope) : $scope;
	}
}
