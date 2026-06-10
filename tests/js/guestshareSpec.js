/*
 * Modified by BW-Tech GmbH
 * @license GPL-2.0
 */

describe('Guest share tests', function() {
	it('shows the concrete share API error', function() {
		spyOn(OC.dialogs, 'alert');

		OCA.Guests.GuestShare.showShareError(
			'Mail transport is unavailable',
			'guest@example.org'
		);

		expect(OC.dialogs.alert).toHaveBeenCalledWith(
			t(
				'guests',
				'Guest {email} could not be invited: {error}',
				{
					email: 'guest@example.org',
					error: 'Mail transport is unavailable'
				}
			),
			t('guests', 'Guest invitation failed')
		);
	});

	it('falls back to a translated share error', function() {
		spyOn(OC.dialogs, 'alert');

		OCA.Guests.GuestShare.showShareError('', 'guest@example.org');

		expect(OC.dialogs.alert).toHaveBeenCalledWith(
			t(
				'guests',
				'Guest {email} could not be invited: {error}',
				{
					email: 'guest@example.org',
					error: t('guests', 'The server did not provide a reason.')
				}
			),
			t('guests', 'Guest invitation failed')
		);
	});
});
