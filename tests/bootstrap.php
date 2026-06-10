<?php
/**
 * Bootstrap file for standalone testing of the Guest plugin
 * This creates mock interfaces for ownCloud Core dependencies
 *
 * Modified by BW-Tech GmbH
 */

declare(strict_types=1);

// Autoload composer dependencies
require_once __DIR__ . '/../vendor/autoload.php';

// Load OCP stubs
require_once __DIR__ . '/stubs/OCP/IConfig.php';
require_once __DIR__ . '/stubs/OCP/IUser.php';
require_once __DIR__ . '/stubs/OCP/IGroup.php';
require_once __DIR__ . '/stubs/OCP/IUserSession.php';
require_once __DIR__ . '/stubs/OCP/IUserManager.php';
require_once __DIR__ . '/stubs/OCP/IGroupManager.php';
require_once __DIR__ . '/stubs/OCP/IURLGenerator.php';
require_once __DIR__ . '/stubs/OCP/ILogger.php';
require_once __DIR__ . '/stubs/OCP/IRequest.php';
require_once __DIR__ . '/stubs/OCP/IL10N.php';
require_once __DIR__ . '/stubs/OCP/IDBConnection.php';
require_once __DIR__ . '/stubs/OCP/Defaults.php';
require_once __DIR__ . '/stubs/OCP/GroupInterface.php';
require_once __DIR__ . '/stubs/OCP/Template.php';
require_once __DIR__ . '/stubs/OCP/Util.php';

// Load OCP Mail stubs
require_once __DIR__ . '/stubs/OCP/Mail/IMailer.php';
require_once __DIR__ . '/stubs/OCP/Mail/IMessage.php';

// Load OCP Share stubs
require_once __DIR__ . '/stubs/OCP/Share/IShare.php';
require_once __DIR__ . '/stubs/OCP/Share/IManager.php';

// Load OCP AppFramework stubs
require_once __DIR__ . '/stubs/OCP/AppFramework/Controller.php';
require_once __DIR__ . '/stubs/OCP/AppFramework/Http/Response.php';
require_once __DIR__ . '/stubs/OCP/AppFramework/Http/JSONResponse.php';
require_once __DIR__ . '/stubs/OCP/AppFramework/Http/DataResponse.php';
require_once __DIR__ . '/stubs/OCP/AppFramework/Http/TemplateResponse.php';
require_once __DIR__ . '/stubs/OCP/AppFramework/Db/DoesNotExistException.php';

// Load OCP Group stubs
require_once __DIR__ . '/stubs/OCP/Group/ISubAdmin.php';
require_once __DIR__ . '/stubs/OCP/Group/Backend/ABackend.php';

// Load OCP Security stubs
require_once __DIR__ . '/stubs/OCP/Security/ISecureRandom.php';

// Load OCP Settings stubs
require_once __DIR__ . '/stubs/OCP/Settings/ISettings.php';

// Load OC stubs
require_once __DIR__ . '/stubs/OC/Hooks.php';
require_once __DIR__ . '/stubs/OC/User/User.php';

// Load Test stubs
require_once __DIR__ . '/stubs/Test/TestCase.php';
