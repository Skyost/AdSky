package fr.skyost.adsky.bukkit;

import com.eclipsesource.json.JsonObject;
import fr.skyost.adsky.bukkit.config.AdSkyBukkitConfiguration;
import fr.skyost.adsky.bukkit.logger.AdSkyBukkitLogger;
import fr.skyost.adsky.core.AbstractAdSkyApplication;
import fr.skyost.adsky.core.AdSkyLogger;

/**
 * Represents the Bukkit implementation of core AdSky.
 */

public class AdSkyBukkitApplication extends AbstractAdSkyApplication {

	/**
	 * The logger.
	 */

	private AdSkyLogger logger;

	/**
	 * The config.
	 */

	private AdSkyBukkitConfiguration config;

	/**
	 * Creates a new application instance.
	 *
	 * @param plugin The bukkit plugin.
	 */

	public AdSkyBukkitApplication(final AdSkyBukkitPlugin plugin) {
		this.logger = new AdSkyBukkitLogger(plugin);
		this.config = new AdSkyBukkitConfiguration(plugin);
	}

	@Override
	public AdSkyLogger getLogger() {
		return logger;
	}

	/**
	 * Sets the logger.
	 *
	 * @param logger The logger.
	 */

	public final void setLogger(final AdSkyLogger logger) {
		this.logger = logger;
	}

	@Override
	public AdSkyBukkitConfiguration getConfiguration() {
		return config;
	}

	/**
	 * Sets the configuration.
	 *
	 * @param config The configuration.
	 */

	public final void setConfiguration(final AdSkyBukkitConfiguration config) {
		this.config = config;
	}

	@Override
	public String getServerPluginKey() {
		return config.serverPluginKey;
	}

	@Override
	public AdSkyBukkitAd createAdFromJSON(JsonObject jsonObject) {
		return AdSkyBukkitAd.fromJSON(config, jsonObject);
	}

}