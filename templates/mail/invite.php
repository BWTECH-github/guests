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
 *   - use the shared owncloud.online mail frame instead of an own 2017 layout
 *   - one call to action as a button, address and expiry as a labelled block
 */

/**
 * Der Rahmen kommt aus dem Kern (html.mail.header/-end), nicht mehr aus dieser
 * Datei. Vorher trug die Einladung ihr eigenes Layout von 2017 - 4-Pixel-
 * Streifen, Verdana in 0,8em, kein Kartenrand - und stand damit neben jeder
 * anderen Mail der Instanz wie ein Fremdkoerper. 'app' => 'core' ist Pflicht,
 * sonst sucht das Blatt die Bausteine im guests-Verzeichnis und bricht mit
 * "template file not found" ab.
 */
print_unescaped($this->inc('html.mail.header', ['app' => 'core']));
?>

<p style="margin:0 0 16px;"><?php p($l->t('Hello,')); ?></p>

<?php if ($_['filename']) { ?>
<p style="margin:0 0 16px;">
	<?php print_unescaped($l->t('%s has shared <strong>%s</strong> with you.', [$_['user_displayname'], $_['filename']])); ?>
</p>
<?php } else { ?>
<p style="margin:0 0 16px;">
	<?php print_unescaped($l->t('%s has shared files with you.', [$_['user_displayname']])); ?>
</p>
<?php } ?>

<p style="margin:0 0 20px;">
	<?php p($l->t('To see them, activate your guest account at %s by setting a password.', [$_['cloud_name']])); ?>
</p>

<?php
/*
 * Die Schaltfläche kommt aus dem Kern, damit Einladung, Freigabemail und
 * Passwortmail dieselbe tragen. Die Beschriftung geht fertig übersetzt hinein:
 * inc() mit 'app' => 'core' tauscht im eingebundenen Blatt auch das
 * l10n-Objekt gegen das des Kerns, ein $l->t() dort fände die Texte von
 * guests also nie.
 */
print_unescaped($this->inc('html.mail.button', [
	'app' => 'core',
	'url' => $_['password_link'],
	'label' => $l->t('Set password'),
	'hint' => $l->t('If the button does not work, open this address:'),
]));
?>

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-collapse:collapse;background-color:#F5F7FA;border-radius:8px;">
<tr>
<td style="padding:14px 18px;font-family:-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;font-size:13px;line-height:1.6;color:#1f2733;">
	<strong><?php p($l->t('Your login')); ?></strong><br>
	<?php p($_['guestEmail']); ?>
	<?php if (isset($_['expiration']) && $_['expiration']) { ?>
	<br><br>
	<strong><?php p($l->t('Expires on')); ?></strong><br>
	<?php p($_['expiration']); ?>
	<?php } ?>
</td>
</tr>
</table>

<?php if ($_['filename'] && $_['link']) { ?>
<p style="margin:20px 0 0;">
	<?php print_unescaped($l->t('After that you can <a href="%s">open the share</a> directly.', [$_['link']])); ?>
</p>
<?php } ?>

<?php
print_unescaped($this->inc('html.mail.end', ['app' => 'core']));
