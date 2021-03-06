<?php
/**
 * phpillow autoload file
 *
 * This file is part of phpillow.
 *
 * phpillow is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Lesser General Public License as published by the Free
 * Software Foundation; version 3 of the License.
 *
 * phpillow is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public License for
 * more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with phpillow; if not, write to the Free Software Foundation, Inc., 51
 * Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package Core
 * @version $Revision: 97 $
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPL
 */

/*
 * This array is autogenerated and topoligically sorted. Do not change anything
 * in here, but just run the following script in the trunk/ directory.
 *
 * # scripts/gen_autoload_files.php
 */
return array(
    'phpillowConnection'                     => 'classes/connection.php',
    'phpillowCustomConnection'               => 'classes/connection/custom.php',
    'phpillowStreamConnection'               => 'classes/connection/stream.php',
    'phpillowDocument'                       => 'classes/document.php',
    'phpillowGroupDocument'                  => 'classes/document/group.php',
    'phpillowUserDocument'                   => 'classes/document/user.php',
    'phpillowException'                      => 'classes/exception.php',
    'phpillowRuntimeException'               => 'classes/exception.php',
    'phpillowConnectionException'            => 'classes/exception.php',
    'phpillowOptionException'                => 'classes/exception.php',
    'phpillowNoDatabaseException'            => 'classes/exception.php',
    'phpillowInvalidRequestException'        => 'classes/exception.php',
    'phpillowNoSuchPropertyException'        => 'classes/exception.php',
    'phpillowValidationException'            => 'classes/exception.php',
    'phpillowResponseErrorException'         => 'classes/exception.php',
    'phpillowResponseNotFoundErrorException' => 'classes/exception.php',
    'phpillowResponseConflictErrorException' => 'classes/exception.php',
    'phpillowManager'                        => 'classes/manager.php',
    'phpillowResponseFactory'                => 'classes/response.php',
    'phpillowResponse'                       => 'classes/response/base.php',
    'phpillowArrayResponse'                  => 'classes/response/array.php',
    'phpillowDataResponse'                   => 'classes/response/data.php',
    'phpillowResultSetResponse'              => 'classes/response/result.php',
    'phpillowStatusResponse'                 => 'classes/response/status.php',
    'phpillowValidator'                      => 'classes/validator.php',
    'phpillowArrayValidator'                 => 'classes/validator/array.php',
    'phpillowDateValidator'                  => 'classes/validator/date.php',
    'phpillowDocumentValidator'              => 'classes/validator/document.php',
    'phpillowDocumentArrayValidator'         => 'classes/validator/document_array.php',
    'phpillowEmailValidator'                 => 'classes/validator/email.php',
    'phpillowFloatValidator'                 => 'classes/validator/float.php',
    'phpillowImageFileLocationValidator'     => 'classes/validator/image_file_location.php',
    'phpillowIndexableDateValidator'         => 'classes/validator/indexable_date.php',
    'phpillowIntegerValidator'               => 'classes/validator/integer.php',
    'phpillowNoValidator'                    => 'classes/validator/no.php',
    'phpillowRegexpValidator'                => 'classes/validator/regexp.php',
    'phpillowStringValidator'                => 'classes/validator/string.php',
    'phpillowTextValidator'                  => 'classes/validator/text.php',
    'phpillowView'                           => 'classes/view.php',
    'phpillowGroupView'                      => 'classes/view/group.php',
    'phpillowUserView'                       => 'classes/view/user.php',
);

