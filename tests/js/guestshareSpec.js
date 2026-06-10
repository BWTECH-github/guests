/*
 * Modified by BW-Tech GmbH
 * @license GPL-2.0
 */

describe('Guest share tests', function() {
	it('shows the concrete share API error', function() {
		spyOn(OC.dialogs, 'alert');

		OCA.Guests.GuestShare.showShareError('Mail transport is unavailable');

		expect(OC.dialogs.alert).toHaveBeenCalledWith(
			'Mail transport is unavailable',
			t('guests', 'Error while sharing')
		);
	});

	it('falls back to a translated share error', function() {
		spyOn(OC.dialogs, 'alert');

		OCA.Guests.GuestShare.showShareError('');

		expect(OC.dialogs.alert).toHaveBeenCalledWith(
			t('guests', 'Error while sharing'),
			t('guests', 'Error while sharing')
		);
	});
});
