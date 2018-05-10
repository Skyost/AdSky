package fr.skyost.adsky.bukkit;

import fr.skyost.adsky.bukkit.config.AdSkyBukkitConfiguration;
import fr.skyost.adsky.bukkit.utils.Skyupdater;
import fr.skyost.adsky.core.AdSkyLogger;
import org.bstats.bukkit.MetricsLite;
import org.bukkit.configuration.InvalidConfigurationException;
import org.bukkit.plugin.java.JavaPlugin;

/**
 * Main plugin class.
 */

public class AdSkyBukkitPlugin extends JavaPlugin {

	private AdSkyBukkitApplication app;

	@Override
	public void onEnable() {
		// Creates a new application instance.
		this.app = new AdSkyBukkitApplication(this);

		// Gets required ad.
		final AdSkyLogger logger = app.getLogger();
		final AdSkyBukkitConfiguration config = app.getConfiguration();

		try {
			// Loads the config.
			logger.message("Loading config...");
			config.load();

			// Enables required options.
			if(config.enableUpdater) {
				new Skyupdater(this, 0, this.getFile(), true, true);
			}
			if(config.enableMetrics) {
				System.setProperty("bstats.relocatecheck", "false");
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
			app.startMainTask();
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
	 * Gets the AdSky Bukkit Application instance.
	 *
	 * @return The AdSky Bukkit Application instance.
	 */

	public final AdSkyBukkitApplication getApplication() {
		return app;
	}

}