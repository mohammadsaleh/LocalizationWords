<?php
class LocalizationWordsActivation {
	public function beforeActivation(&$controller) {
		return true;
	}

	public function onActivation(&$controller) {
            return true;
	}

	public function beforeDeactivation(&$controller) {
		return true;
	}

	public function onDeactivation(&$controller) {
            return true;
	}
}
