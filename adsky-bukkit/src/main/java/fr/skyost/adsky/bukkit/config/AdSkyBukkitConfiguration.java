package fr.skyost.adsky.bukkit.config;

import fr.skyost.adsky.bukkit.utils.Skyoconfig;
import fr.skyost.adsky.core.AdSkyConfiguration;
import org.bukkit.plugin.Plugin;

import java.io.File;
import java.util.Arrays;
import java.util.List;

/**
 * Default plugin configuration.
 */

public class AdSkyBukkitConfiguration extends Skyoconfig implements AdSkyConfiguration {

	@ConfigOptions(name = "enable.updater")
	public final boolean enableUpdater = true;
	@ConfigOptions(name = "enable.metrics")
	public final boolean enableMetrics = true;

	@ConfigOptions(name = "server.url")
	public final String serverUrl = "http://yourwebsite.com/adsky/";
	@ConfigOptions(name = "server.plugin-key")
	public final String serverPluginKey = "Paste your plugin key here.";
	@ConfigOptions(name = "server.event-scheduled")
	public final boolean serverEventScheduled = false;

	@ConfigOptions(name = "ads.preferred-hour")
	public int adsPreferredHour = 12;
	@ConfigOptions(name = "ads.distribution-function")
	public final String adsDistributionFunction = "(sqrt(n/2)/log10(n)) * e^(-((x-h)^2) / (2*log10(n)))";
	@ConfigOptions(name = "ads.world-blacklist")
	public final List<String> adsWorldBlackList = Arrays.asList("WorldA", "WorldB", "WorldC");

	/**
	 * Creates a new plugin configuration instance.
	 *
	 * @param plugin The plugin.
	 */

	public AdSkyBukkitConfiguration(final Plugin plugin) {
		super(new File(plugin.getDataFolder(), "config.yml"), Arrays.asList(plugin.getName() + " configuration file"));
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