package fr.skyost.adsky.core;

/**
 * Represents an AdSky configuration.
 */

public interface AdSkyConfiguration {

	/**
	 * Gets the server URL.
	 *
	 * @return The server URL.
	 */

	String getServerURL();

	/**
	 * Gets if the main task should automatically delete ads.
	 *
	 * @return Whether the main task should automatically delete ads.
	 */

	boolean shouldAutoDeleteAds();

	/**
	 * Gets the ads distribution function.
	 *
	 * @return The ads distribution function.
	 */

	String getAdsDistributionFunction();

	/**
	 * Gets the preferred hour to show ads.
	 *
	 * @return The preferred hour to show ads.
	 */

	int getAdsPreferredHour();

}