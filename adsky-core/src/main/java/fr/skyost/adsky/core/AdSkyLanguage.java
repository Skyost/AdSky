package fr.skyost.adsky.core;

import java.util.Calendar;

public interface AdSkyLanguage {

	/**
	 * Translation of "Getting ads...".
	 *
	 * @return The translation.
	 */

	String gettingAds();

	/**
	 * Translation of "Found %d ad(s) to broadcast today.".
	 *
	 * @param adsNumber Number of ads.
	 *
	 * @return The translation.
	 */

	String foundAds(final int adsNumber);

	/**
	 * Translation of "Broadcasting a random ad from list...".
	 *
	 * @return The translation.
	 */

	String broadcastingRandomAd();

	/**
	 * Translation of "Success !".
	 *
	 * @return The translation.
	 */

	String success();

	/**
	 * Translation of "Scheduled next ad broadcast (if available) on %s.".
	 *
	 * @param date The schedule date.
	 *
	 * @return The translation.
	 */

	String scheduledAt(final Calendar date);

	/**
	 * Translation of "Deleting expired ads...".
	 *
	 * @return The translation.
	 */

	String deletingExpiredAds();

	/**
	 * Translation of "Unable to delete expired ads :".
	 *
	 * @return The translation.
	 */

	String unableDeleteExpiredAds();

	/**
	 * Translation of "Unable to request ads :".
	 *
	 * @return The translation.
	 */

	String unableRequestAds();

	/**
	 * Translation of "Invalid response code : %d.".
	 *
	 * @param responseCode The response code.
	 *
	 * @return The translation.
	 */

	String invalidResponseCode(final int responseCode);

}