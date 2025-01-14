<?php

if ( !class_exists('Puc_v4p4_DebugBar_PluginPanel', false) ):

	/**
	 * Class Puc_v4p4_DebugBar_PluginPanel
	 */
	class Puc_v4p4_DebugBar_PluginPanel extends Puc_v4p4_DebugBar_Panel {
		/**
		 * @var Puc_v4p4_Plugin_UpdateChecker
		 */
		protected $updateChecker;

		protected function displayConfigHeader() {
			$this->row('Plugin file', htmlentities($this->updateChecker->pluginFile));
			parent::displayConfigHeader();
		}

		/**
		 * @return string
		 */
		protected function getMetadataButton() {
			$requestInfoButton = '';
			if ( function_exists('get_submit_button') ) {
				$requestInfoButton = get_submit_button(
					'Request Info',
					'secondary',
					'puc-request-info-button',
					false,
					array('id' => $this->updateChecker->getUniqueName('request-info-button'))
				);
			}
			return $requestInfoButton;
		}

		/**
		 * @return array
		 */
		protected function getUpdateFields() {
			return array_merge(
				parent::getUpdateFields(),
				array('homepage', 'upgrade_notice', 'tested',)
			);
		}
	}

endif;