package fr.skyost.adsky.core.lang;

import fr.skyost.adsky.core.AdSkyLanguage;

import java.util.Calendar;

public class AdSkyEnglishLanguage implements AdSkyLanguage {

	@Override
	public String gettingAds() {
		return "Getting ads...";
	}

	@Override
	public String foundAds(final int adsNumber) {
		return "Found " + adsNumber + " ad(s) to broadcast today.";
	}

	@Override
	public String broadcastingRandomAd() {
		return "Broadcasting a random ad from list...";
	}

	@Override
	public String success() {
		return "Success !";
	}

	@Override
	public String scheduledAt(final Calendar date) {
		return "Scheduled next ad broadcast (if available) on " + date.getTime() + ".";
	}

	@Override
	public String deletingExpiredAds() {
		return "Deleting expired ads...";
	}

	@Override
	public String unableDeleteExpiredAds() {
		return "Unable to delete expired ads :";
	}

	@Override
	public String unableRequestAds() {
		return "Unable to request ads :";
	}

	@Override
	public String invalidResponseCode(final int responseCode) {
		return "Invalid response code : " + responseCode + ".";
	}

}