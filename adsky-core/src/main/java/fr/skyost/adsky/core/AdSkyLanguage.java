package fr.skyost.adsky.core;

import java.util.Calendar;

public interface AdSkyLanguage {

	/**
	 * Translation of "Getting ads...".
	 *
	 * @return The translation.
	 */

	public String gettingAds();

	/**
	 * Translation of "Found %d ad(s) to broadcast today.".
	 *
	 * @param adsNumber Number of ads.
	 *
	 * @return The translation.
	 */

	public String foundAds(final int adsNumber);

	/**
	 * Translation of "Broadcasting a random ad from list...".
	 *
	 * @return The translation.
	 */

	public String broadcastingRandomAd();

	/**
	 * Translation of "Success !".
	 *
	 * @return The translation.
	 */

	public String success();

	/**
	 * Translation of "Scheduled next ad broadcast (if available) on %s.".
	 *
	 * @param date The schedule date.
	 *
	 * @return The translation.
	 */

	public String scheduledAt(final Calendar date);

	/**
	 * Translation of "Deleting expired ads...".
	 *
	 * @return The translation.
	 */

	public String deletingExpiredAds();

	/**
	 * Translation of "Unable to delete expired ads :".
	 *
	 * @return The translation.
	 */

	public String unableDeleteExpiredAds();

	/**
	 * Translation of "Unable to request ads :".
	 *
	 * @return The translation.
	 */

	public String unableRequestAds();

	/**
	 * Translation of "Invalid response code : %d.".
	 *
	 * @param responseCode The response code.
	 *
	 * @return The translation.
	 */

	public String invalidResponseCode(final int responseCode);

}