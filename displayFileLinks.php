<?php
/**
 *  Copyright notice
 *
 *  ⓒ 2014 Michiel Roos <michiel@maxserv.nl>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is free
 *  software; you can redistribute it and/or modify it under the terms of the
 *  GNU General Public License as published by the Free Software Foundation;
 *  either version 2 of the License, or (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 *  or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for
 *  more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

/**
 * FAL Support
 *
 * @param   array $markerArray : array filled with markers from the
 *    getItemMarkerArray function in tt_news class. see:
 *    EXT:tt_news/pi/class.tx_ttnews.php
 * @param   [type]      $conf: ...
 *
 * @return   array      the changed markerArray
 */
function user_displayFileLinks($markerArray, $conf) {
	$pObj = &$conf['parentObj']; // make a reference to the parent-object
	$row = $pObj->local_cObj->data;
	$markerArray['###FILE_LINK###'] = '';
	$markerArray['###TEXT_FILES###'] = '';

	//load TS config for newsFiles from tt_news
	$conf_newsFiles = $pObj->conf['newsFiles.'];
	//Important: unset path
	$conf_newsFiles['path'] = '';

	$local_cObj = t3lib_div::makeInstance('tslib_cObj');

	//workspaces
	if (isset($row['_ORIG_uid']) && ($row['_ORIG_uid'] > 0)) {
		// draft workspace
		$uid = $row['_ORIG_uid'];
	} else {
		// live workspace
		$uid = $row['uid'];
	}
	// Check for translation
	if ($row['_LOCALIZED_UID']) {
		//i10n mode = mergeIfNotBlank
		if ($row['tx_falttnews_fal_media']) {
			$uid = $row['_LOCALIZED_UID'];
		}
	}

	$fileRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\FileRepository');
	$fileObjects = $fileRepository->findByRelation('tt_news', 'tx_falttnews_fal_media', $uid);

	// get Imageobject information
	$files = array();
	foreach ($fileObjects as $key => $value) {
		$files[$key]['reference'] = $value->getReferenceProperties();
		$files[$key]['original'] = $value->getOriginalFile()->getProperties();
	}

	foreach ($files as $key => $value) {
		var_dump($value['reference']['name']);
	}

	// localisation of FAL data
	$damFiles = array('files' => array());

	if (is_array($damFiles)) {
		$files_stdWrap = t3lib_div::trimExplode('|', $pObj->conf['newsFiles_stdWrap.']['wrap']);

		$filelinks = '';
		while (list($key, $val) = each($damFiles['files'])) {
			if ($val) {
				$local_cObj->start($damFiles['rows'][$key]);
				$filelinks .= $local_cObj->filelink($val, $conf_newsFiles);
			}
		}
		if ($filelinks) {
			$markerArray['###FILE_LINK###'] = $filelinks . $files_stdWrap[1];
			$markerArray['###TEXT_FILES###'] = $files_stdWrap[0] . $pObj->local_cObj->stdWrap($pObj->pi_getLL('textFiles'), $pObj->conf['newsFilesHeader_stdWrap.']);
		}
	}

	return $markerArray;
}