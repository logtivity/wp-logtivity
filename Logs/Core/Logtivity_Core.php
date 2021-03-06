<?php

class Logtivity_Core extends Logtivity_Abstract_Logger
{
	public function registerHooks()
	{
		add_action( 'upgrader_process_complete', [$this, 'upgradeProcessComplete'], 10, 2);
		add_action( 'wp_update_nav_menu', [$this, 'menuUpdated'], 10, 2 );
		add_filter( 'widget_update_callback', [$this, 'widgetUpdated'], 10, 4);
		add_action('init', [$this, 'maybeSettingsUpdated']);
		add_action( 'permalink_structure_changed', [$this, 'permalinksUpdated'], 10, 2);
	}

	public function upgradeProcessComplete( $upgrader_object, $options ) 
	{
	    if ( $options['type'] != 'core' ) {
	    	return;
	    }

		if ($options['action'] == 'update') {
			return $this->coreUpdated($upgrader_object, $options);
		}

		if ($options['action'] == 'install') {
			return $this->coreInstalled($upgrader_object, $options);
		}
	}

	public function coreUpdated($upgrader_object, $options)
	{
		return Logtivity_Logger::log('WP Core Updated');
	}

	public function coreInstalled($upgrader_object, $options)
	{
		return Logtivity_Logger::log('WP Core Installed');
	}

	public function menuUpdated($nav_menu_id, $menuData = [])
	{
		if (isset($menuData['menu-name'])) {
			return Logtivity_Logger::log()
				->setAction('Menu Updated')
				->setContext($menuData['menu-name'])
				->addMeta('Menu ID', $nav_menu_id)
				->send();
		}
	}

	public function widgetUpdated($instance, $new, $old, $obj)
	{
		Logtivity_Logger::log()
			->setAction('Widget Updated')
			->setContext($obj->name)
			->addMeta('New Content', $new)
			->addMeta('Old Content', $old)
			->send();

		return $instance;
	}

	public function maybeSettingsUpdated()
	{
		if (!isset($_POST['option_page']) || !$_POST['option_page'] || !isset($_POST['action']) || $_POST['action'] != 'update') {
			return;
		}
		if (!in_array($_POST['option_page'], ['writing', 'general', 'reading', 'discussion', 'media'])) {
			return;
		}

		Logtivity::log()
			->setAction('Settings Updated')
			->setContext('Core:'.$_POST['option_page'])
			->send();
	}

	public function permalinksUpdated($old_permalink_structure, $permalink_structure)
	{
		Logtivity::log()
			->setAction('Permalinks Updated')
			->setContext($this->getPermalinkStructure($permalink_structure))
			->addMeta('Old Structure', $this->getPermalinkStructure($old_permalink_structure))
			->send();
	}

	private function getPermalinkStructure($value)
	{
		return ( $value == '' ? 'Plain' : $value);
	}
}

$Logtivity_Core = new Logtivity_Core;