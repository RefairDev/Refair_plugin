<?php
/**
 * The plugin meta creation parameter class File.
 *
 * @link       pixelscodex.com
 * @since      1.0.0
 *
 * @package    Refairplugin
 * @subpackage Refairplugin/admin
 */

namespace Refairplugin;

/**
 *  The plugin meta creation parameter class.
 *
 * @link       pixelscodex.com
 * @since      1.0.0
 *
 * @package    Refairplugin
 * @subpackage Refairplugin/admin
 */
class Refairplugin_Meta_Parameters {
	/**
	 * Name for the meta.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Title for the meta.
	 *
	 * @var [type]
	 */
	public $title;

	/**
	 * Type of the meta.
	 *
	 * @var string
	 */
	public $type;

	/**
	 * Array of options for meta creation.
	 *
	 * @var array
	 */
	public $options;

	/**
	 * Constructor set internal variables.
	 *
	 * @param  string $type Name for the meta.
	 * @param  string $name Title for the meta.
	 * @param  string $title Type of the meta.
	 * @param  array  $options Array of options for meta creation.
	 */
	public function __construct(
		$type,
		$name,
		$title,
		$options = array()
	) {
		$this->name    = $name;
		$this->title   = $title;
		$this->type    = $type;
		$this->options = $options;
	}
}
