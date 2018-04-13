package fr.skyost.adsky;

import fr.skyost.adsky.config.PluginConfig;
import fr.skyost.adsky.logger.PluginLogger;
import fr.skyost.adsky.tasks.BroadcastAdsTask;
import fr.skyost.adsky.utils.Skyupdater;
import org.bstats.bukkit.MetricsLite;
import org.bukkit.configuration.InvalidConfigurationException;
import org.bukkit.plugin.java.JavaPlugin;

/**
 * Main class of the plugin.
 */

public class AdSky extends JavaPlugin {

	/**
	 * The logger.
	 */

	private PluginLogger logger;

	/**
	 * The configuration.
	 */

	private PluginConfig config;

	@Override
	public void onEnable() {
		// Creates a new logger instance.
		logger = new PluginLogger(this);

		try {
			// Loads the configuration.
			logger.message("Loading config...");
			config = new PluginConfig(this);
			config.load();

			// Enables required options.
			if(config.enableUpdater) {
				new Skyupdater(this, 0, this.getFile(), true, true);
			}
			if(config.enableMetrics) {
				new MetricsLite(this);
			}

			// Let's check if the user has misconfigured the plugin.
			boolean hasChanges = false;
			if(config.adsPreferredHour < 0 || config.adsPreferredHour > 23) {
				config.adsPreferredHour = 12;
				hasChanges = true;
			}

			if(hasChanges) {
				logger.error("Invalid config detected, changed it.");
				config.save();
			}
			else {
				logger.success("Config loaded with success !");
			}

			// Then we can start the main task.
			new Thread(new BroadcastAdsTask(this)).start();
		}
		catch(final InvalidConfigurationException ex) {
			logger.error("Unable to load config file. Try to delete it and restart your server.");
			ex.printStackTrace();
		}
		catch(final Exception ex) {
			logger.error("Unable to load plugin file. Please submit this error on https://github.com/Skyost/AdSky/issues.");
			ex.printStackTrace();
		}
	}

	/**
	 * Gets the logger.
	 *
	 * @return The logger.
	 */

	public PluginLogger getAdSkyLogger() {
		return logger;
	}

	/**
	 * Gets the configuration.
	 *
	 * @return The configuration.
	 */

	public PluginConfig getAdSkyConfig() {
		return config;
	}

}