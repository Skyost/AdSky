package fr.skyost.adsky.bukkit.config;

import fr.skyost.adsky.bukkit.utils.Skyoconfig;
import fr.skyost.adsky.core.AdSkyConfiguration;
import org.bukkit.plugin.Plugin;

import java.io.File;
import java.util.Arrays;
import java.util.List;

public class AdSkyBukkitConfiguration extends Skyoconfig implements AdSkyConfiguration {

	@ConfigOptions(name = "enable.updater")
	public boolean enableUpdater = true;
	@ConfigOptions(name = "enable.metrics")
	public boolean enableMetrics = true;

	@ConfigOptions(name = "server.url")
	public String serverUrl = "http://yourwebsite.com/adsky/";
	@ConfigOptions(name = "server.plugin-key")
	public String serverPluginKey = "Paste your plugin key here.";
	@ConfigOptions(name = "server.event-scheduled")
	public boolean serverEventScheduled = false;

	@ConfigOptions(name = "ads.preferred-hour")
	public int adsPreferredHour = 12;
	@ConfigOptions(name = "ads.distribution-function")
	public String adsDistributionFunction = "(sqrt(n/2)/log10(n)) * e^(-((x-h)^2) / (2*log10(n)))";
	@ConfigOptions(name = "ads.world-blacklist")
	public List<String> adsWorldBlackList = Arrays.asList("WorldA", "WorldB", "WorldC");

	/**
	 * Creates a new plugin configuration instance.
	 *
	 * @param plugin The plugin.
	 */

	public AdSkyBukkitConfiguration(final Plugin plugin) {
		super(new File(plugin.getDataFolder(), "config.yml"), Arrays.asList(plugin.getName() + " configuration file"));
	}

	@Override
	public String getServerURL() {
		return serverUrl;
	}

	@Override
	public boolean shouldAutoDeleteAds() {
		return !serverEventScheduled;
	}

	@Override
	public String getAdsDistributionFunction() {
		return adsDistributionFunction;
	}

	@Override
	public int getAdsPreferredHour() {
		return adsPreferredHour;
	}

}