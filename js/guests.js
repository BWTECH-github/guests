/**
 * @author Jörn Friedrich Dreyer <jfd@owncloud.com>
 * @author Thomas Heinisch <t.heinisch@bw-tech.de>
 * @author Vincent Petry <pvince81@owncloud.com>
 *
 * @copyright Copyright (c) 2018, ownCloud GmbH
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
(function(OC, OCA) {
	if (!OCA.Guests) {
		OCA.Guests = {};
	}

	OCA.Guests.initSettingsPage = function() {
		// variables
		var $section = $('#guests');
		var $guestsByGroup = $section.find('#guestsByGroup');
		var $guestGroup = $section.find('#guestGroup');
		var $guestSharingBlockDomains = $section.find('#guestSharingBlockDomains');
		var $guestUseWhitelist = $section.find('#guestUseWhitelist');
		var $guestWhitelist = $section.find('#guestWhitelist');
		var $resetWhitelist = $section.find('#guestResetWhitelist');
		var $msg = $section.find('.msg');

		// functions

		// Sperrliste und Erlaubnisliste sind seit 1.0.5 mehrzeilige Textfelder
		// (eine lange Liste lief im einzeiligen Feld aus der Karte). Einträge
		// trennt ein Komma, ein Leerzeichen oder ein Zeilenumbruch; gespeichert
		// wird wie bisher kommagetrennt ohne Leerzeichen.
		var listeAusText = function (text) {
			return $.grep(String(text).split(/[\s,]+/), function (eintrag) {
				return eintrag !== '';
			});
		};

		var loadConfig = function () {
			OC.msg.startAction($msg, t('guests', 'Loading…'));
			$.get(
				OC.generateUrl('apps/guests/config'),
				'',
				function (data) {
					// update model
					config = data;
					// update ui
					if (config.useWhitelist) {
						$guestUseWhitelist.prop('checked', true);
						$guestWhitelist.show();
						$resetWhitelist.show();
					} else {
						$guestUseWhitelist.prop('checked', false);
						$guestWhitelist.hide();
						$resetWhitelist.hide();
					}
					if (config.group) {
						$guestGroup.val(config.group);
					} else {
						$guestGroup.val('');
					}
					// Mit Leerzeichen nach dem Komma, damit die Liste im
					// Textfeld zwischen den Apps umbricht.
					if ($.isArray(config.whitelist)) {
						$guestWhitelist.val(config.whitelist.join(', '));
					} else {
						$guestWhitelist.val('');
					}
					if (config.shareBlockDomains) {
						$guestSharingBlockDomains.val(config.shareBlockDomains);
					}
				},
				'json'
			).then(function() {
					var data = { status: 'success',	data: {message: t('guests', 'Loaded')} };
					OC.msg.finishedAction($msg, data);
				}, function(result) {
					var data = { status: 'error', data:{message:result.responseJSON.message} };
					OC.msg.finishedAction($msg, data);
				});
		};

		var saveConfig = function () {
			OC.msg.startSaving($msg);
			$.ajax({
				type: 'PUT',
				url: OC.generateUrl('apps/guests/config'),
				data: config,
				dataType: 'json'
			}).success(function(data) {
				OC.msg.finishedSaving($msg, data);
			}).fail(function(result) {
				var data = { status: 'error', data:{message:result.responseJSON.message} };
				OC.msg.finishedSaving($msg, data);
			});
		};

		// load initial config
		loadConfig();

		var updateConditions = function () {
			var conditions = [];

			if ($guestsByGroup.prop('checked')) {
				conditions.push('group');
			}
			config.conditions = conditions;
		};
		
		var saveGroup = function () {
			config.group = $guestGroup.val();
			saveConfig();			
		}
		
		var saveWhitelist = function () {
			config.whitelist = listeAusText($guestWhitelist.val());
			// Eine leere Liste schickt jQuery gar nicht mit; der Controller
			// verlangt den Parameter. Wie bisher ein leerer Eintrag.
			if (config.whitelist.length === 0) {
				config.whitelist = [''];
			}
			saveConfig();
		}

		var saveShareBlockDomains = function () {
			// FIXME: do validations here to make sure valid input is passed
			//        and valid domains
			config.shareBlockDomains = listeAusText($guestSharingBlockDomains.val()).join(',');
			saveConfig();
		}

		// listen to ui changes
		$guestsByGroup.on('change', function () {
			updateConditions();
			saveConfig();
		});

		$guestGroup.on('change', function () {
			saveGroup();
		});
		
		$guestGroup.keypress(function (e) {
			var key = e.which;
			if (key == 13) {
				saveGroup();
				return true;
			}
		});
		
		$guestUseWhitelist.on('change', function () {
			config.useWhitelist = $guestUseWhitelist.prop('checked');
			if(config.useWhitelist) {
				$guestWhitelist.show();
				$resetWhitelist.show()
			} else {
				$guestWhitelist.hide();
				$resetWhitelist.hide();
			}
			saveConfig();
		});
		
		$guestWhitelist.on('change', function () {
			saveWhitelist();
		});
		
		$guestSharingBlockDomains.on('change', function () {
			saveShareBlockDomains();
		});
		
		// Eingabetaste speichert wie im früheren einzeiligen Feld und fügt
		// keinen Zeilenumbruch ein.
		$guestWhitelist.keypress(function (e) {
			var key = e.which;
			if (key == 13) {
				e.preventDefault();
				saveWhitelist();
			}
		});
		
		$resetWhitelist.on('click', function () {
			OC.msg.startSaving($msg);
			$.ajax({
				type: 'POST',
				url: OC.generateUrl('apps/guests/whitelist/reset')
			}).success(function(response) {
				config.whitelist = response.whitelist;
				//update ui
				if ($.isArray(config.whitelist)) {
					$guestWhitelist.val(config.whitelist.join(', '));
				} else {
					$guestWhitelist.val('');
				}
				OC.msg.finishedSaving($msg, {
					status:'success',
					data: { message:t('guests', 'Reset') }
				});
			}).fail(function(response) {
				OC.msg.finishedSaving($msg, {
					status: 'error',
					data: { message: response.responseJSON.message }
				});
			});
		});
		
	};

})(OC, OCA);
