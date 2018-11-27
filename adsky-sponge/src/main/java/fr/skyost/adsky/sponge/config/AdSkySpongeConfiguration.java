package fr.skyost.adsky.sponge.config;

import fr.skyost.adsky.core.AdSkyConfiguration;
import ninja.leaping.configurate.objectmapping.serialize.ConfigSerializable;

import java.nio.file.Path;
import java.util.Arrays;
import java.util.List;

/**
 * Default plugin configuration.
 */

@ConfigSerializable
public class AdSkySpongeConfiguration extends SpongeConfig implements AdSkyConfiguration {

	@ConfigOptions(name = "enable.updater")
	public boolean enableUpdater = true;

	@ConfigOptions(name = "server.url")
	public String serverUrl = "http://yourwebsite.com/adsky/";
	@ConfigOptions(name = "server.plugin-key")
	public String serverPluginKey = "Paste your plugin key here.";
	@ConfigOptions(name = "server.event-scheduled")
	public boolean serverEventScheduled = false;

	@ConfigOptions(name = "ads.preferred-hour")
	public int adsPreferredHour = 12;
	@ConfigOptions(name = "ads.distribution-function")
	public String adsDistributionFunction = "(SQRT(n/2)/LOG10(n+2)) * e^(-((x-h)^2) / (2*LOG10(n+2)))";
	@ConfigOptions(name = "ads.world-blacklist")
	public List<String> adsWorldBlackList = Arrays.asList("WorldA", "WorldB", "WorldC");

	/**
	 * Creates a new plugin configuration instance.
	 *
	 * @param file The config file.
	 */

	public AdSkySpongeConfiguration(final Path file) {
		super(file, "AdSky Configuration");
	}

	@Override
	public final String getServerURL() {
		return serverUrl;
	}

	@Override
	public final boolean shouldAutoDeleteAds() {
		return !serverEventScheduled;
	}

	@Override
	public final String getAdsDistributionFunction() {
		return adsDistributionFunction;
	}

	@Override
	public final int getAdsPreferredHour() {
		return adsPreferredHour;
	}

}