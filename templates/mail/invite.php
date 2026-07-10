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
 */
?>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tr><td>
<table cellspacing="0" cellpadding="0" border="0" width="600px">
<tr>
<td colspan="2" style="padding:0;">
<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tr><td style="height:4px;line-height:4px;font-size:0;background-color:<?php p($theme->getMailHeaderColor());?>;">&nbsp;</td></tr>
<tr><td align="center" style="padding:26px 0 14px;background-color:#ffffff;"><img src="<?php p(\OC::$server->getURLGenerator()->getAbsoluteURL(image_path('', 'logo-mail.gif'))); ?>" alt="<?php p($theme->getName()); ?>" style="display:block;margin:0 auto;border:0;max-width:210px;height:auto;"></td></tr>
</table>
</td>
</tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr>
<td width="20px">&nbsp;</td>
<td style="font-weight:normal; font-size:0.8em; line-height:1.2em; font-family:verdana,'arial',sans;">
<?php
if ($_['filename']) {
	print_unescaped($l->t(
		'Hey there,<br><br>
         
         just letting you know that %s shared <strong>%s</strong> with you.<br><br>
         Activate your guest account at %s by <a href="%s">setting a password</a>.<br><br>
         Then <a href="%s">view it!</a><br><br>You can login using the email address <strong>"%s"</strong> .<br><br>',
		[$_['user_displayname'], $_['filename'], $_['cloud_name'], $_['password_link'], $_['link'], $_['guestEmail']]
	));
} else {
	print_unescaped($l->t(
		'Hey there,<br><br>
         
         just letting you know that %s shared files with you.<br><br>
         Activate your guest account at %s by <a href="%s">setting a password</a>.<br><br>
         <br><br>You can login using the email address <strong>"%s"</strong> .<br><br>',
		[$_['user_displayname'], $_['cloud_name'], $_['password_link'], $_['guestEmail']]
	));
}

if ( isset($_['expiration']) ) {
	p($l->t("The share will expire on %s.", array($_['expiration'])));
	print_unescaped('<br><br>');
}
?>
</td>
</tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr>
	<td width="20px">&nbsp;</td>
	<td style="font-weight:normal; font-size:0.8em; line-height:1.2em; font-family:verdana,'arial',sans;">
		<?php print_unescaped($this->inc('html.mail.footer', ['app' => 'core'])); ?>
	</td>
</tr>
<tr>
	<td colspan="2">&nbsp;</td>
</tr>
</table>
</td></tr>
</table>
