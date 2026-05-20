<?php

namespace Outstand\WP\Icons;

class Plugin {

	/**
	 * Singleton instance of the Plugin.
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * Registered module instances, keyed by class name.
	 *
	 * @var array<class-string, BaseModule>
	 */
	private array $modules = [];

	/**
	 * Returns singleton instance.
	 *
	 * @return Plugin The singleton instance.
	 */
	public static function get_instance(): Plugin {

		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Enable plugin functionality.
	 *
	 * @return void
	 */
	public function enable(): void {

		$modules = [
			new Assets(),
			new InlineIcon(),
		];

		foreach ( $modules as $module ) {
			if ( $module instanceof BaseModule && $module->can_register() ) {
				$module->register();
				$this->modules[ $module::class ] = $module;
			}
		}
	}

	/**
	 * Retrieve a registered module instance by class name.
	 *
	 * @template T of BaseModule
	 *
	 * @param  string $class_name Module class name.
	 * @phpstan-param class-string<T> $class_name
	 *
	 * @return T|null
	 */
	public function get_module( string $class_name ): ?BaseModule {
		return $this->modules[ $class_name ] ?? null;
	}

	/**
	 * Private constructor to enforce singleton pattern.
	 */
	private function __construct() {}
}
