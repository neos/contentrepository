<?php
declare(ENCODING = 'utf-8');

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 * @package TYPO3CR
 * @subpackage NodeType
 * @version $Id$
 */

/**
 * A NodeTypeIterator
 *
 * @package TYPO3CR
 * @subpackage NodeType
 * @version $Id$
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @scope prototype
 */
class F3_TYPO3CR_NodeType_NodeTypeIterator extends F3_TYPO3CR_RangeIterator implements F3_PHPCR_NodeType_NodeTypeIteratorInterface {

	/**
	 * Returns the next NodeType in the iteration.
	 *
	 * @return F3_PHPCR_NodeType_NodeTypeInterface
	 * @throws OutOfBoundsException if the iterator contains no more elements.
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	public function nextNodeType() {
		return $this->next();
	}
}
?>