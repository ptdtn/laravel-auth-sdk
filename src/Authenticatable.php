<?php

namespace PTDTN\Auth;

interface Authenticatable {
	/**
	 * Get the name of the unique identifier for the PTDTN user.
	 *
	 * @return string
	 */
	public function getPTDTNAuthIdentifierName();

	/**
	 * Create user from PTDTN user info.
	 *
	 * @param  \PTDTN\Auth\User  $user
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function createFromPTDTNUser(User $user);
}