<?php
/**
 * File containing Refairplugin_Xls_Generator_Worker class
 *
 * @link       pixelscodex.com
 * @since      1.0.0
 *
 * @package    Refairplugin
 * @subpackage Refairplugin/public
 * @author     Thomas Vias <t.vias@pixelscodex.com>
 */

use Refairplugin\Refairplugin_Files_Generator_Input;

/**
 * Class managing xls files gneration asynchronously.
 */
class Refairplugin_Xls_Generator_Worker extends WP_Background_Process {

	/**
	 * Name of the worker.
	 *
	 * @var string
	 */
	protected $action = 'xls_generator_worker';

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return mixed
	 */
	protected function task( $item ) {
		try {
			sleep( 0.75 );
			$args = json_decode( $item, true );
			if ( array_key_exists( 'id', $args ) && false === get_post_status( $args['id'] ) ) {
				return false;
			}
			$inputs = new Refairplugin_Files_Generator_Input( $args );
			( new Refairplugin_Xls_Generator( $inputs ) )->generate_xls();
		} catch ( Throwable $t ) {
			$this->write_log( 'XLS generation error:' . $t->getMessage() );
			return $item;
		}
		return false;
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {

		parent::complete();
		// Show notice to user or perform some other arbitrary task...
	}

	/**
	 * Unlock worker process.
	 *
	 * @return void
	 */
	public function unlock() {
		$this->unlock_process();
	}

	/**
	 * Write message in log.
	 *
	 * @param  string $log Message to log.
	 * @return void
	 */
	public function write_log( $log ) {
		if ( true === WP_DEBUG ) {
			if ( is_array( $log ) || is_object( $log ) ) {
				error_log( print_r( $log, true ) );
			} else {
				error_log( $log );
			}
		}
	}

	/**
	 * Is worker is processing
	 *
	 * @return boolean True if processing else false.
	 */
	public function is_processing() {
		return $this->is_process_running();
	}

	/**
	 * Get count of queued tasks.
	 *
	 * @return int Tasks count.
	 */
	public function get_task_count() {

		$task_count = 0;
		if ( property_exists( $this, 'data' ) && is_array( $this->data ) ) {
			$task_count = count( $this->data );
		}
		return $task_count;
	}

	/**
	 * Clear tasks queue.
	 *
	 * @return Refairplugin_Xls_Generator_Worker Current worker instance.
	 */
	public function clear_queue() {
		$this->data = array();
		return $this;
	}
}
