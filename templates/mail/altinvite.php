<?php
/**
 * @author Ilja Neumann <ineumann@owncloud.com>
 * @author Jörn Friedrich Dreyer <jfd@butonic.de>
 *
 * @copyright Copyright (c) 2017, ownCloud GmbH
 * @license GPL-2.0
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright Copyright (c) 2026, BW-Tech GmbH
 *
 * Modified by BW-Tech GmbH on 2026-09-15.
 * Changes:
 *   - follow the HTML invitation: same order, same wording
 *   - handle an invitation without a share (sendGuestPlainInviteMail)
 */

/**
 * Textfassung der Einladung. Sie folgt derselben Reihenfolge wie die
 * HTML-Fassung, damit ein Empfaenger, dessen Programm nur Text anzeigt,
 * dieselbe Mail liest.
 *
 * Der alte Text nannte Dateinamen und Verweis bedingungslos. Bei einer
 * Einladung ohne Freigabe (sendGuestPlainInviteMail uebergibt beides als null)
 * stand dort ein Satz mit leerem Namen und ein leerer Verweis.
 */
print_unescaped($l->t('Hello,'));
print_unescaped("\n\n");

if ($_['filename']) {
	print_unescaped($l->t('%s has shared "%s" with you.', [$_['user_displayname'], $_['filename']]));
} else {
	print_unescaped($l->t('%s has shared files with you.', [$_['user_displayname']]));
}
print_unescaped("\n\n");

print_unescaped($l->t('To see them, activate your guest account at %s by setting a password.', [$_['cloud_name']]));
print_unescaped("\n\n");

print_unescaped($l->t('Set password:'));
print_unescaped("\n" . $_['password_link'] . "\n\n");

print_unescaped($l->t('Your login: %s', [$_['guestEmail']]));
print_unescaped("\n");

if (isset($_['expiration']) && $_['expiration']) {
	print_unescaped($l->t('Expires on: %s', [$_['expiration']]));
	print_unescaped("\n");
}

if ($_['filename'] && $_['link']) {
	print_unescaped("\n");
	print_unescaped($l->t('After that you can open the share directly:'));
	print_unescaped("\n" . $_['link'] . "\n");
}
print_unescaped("\n");
?>
<?php print_unescaped($this->inc('plain.mail.footer', ['app' => 'core']));
