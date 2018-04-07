package fr.skyost.adsky;

import fr.skyost.adsky.config.PluginConfig;
import fr.skyost.adsky.logger.PluginLogger;
import fr.skyost.adsky.tasks.BroadcastAdsTask;
import org.bukkit.ChatColor;
import org.bukkit.configuration.InvalidConfigurationException;
import org.bukkit.plugin.java.JavaPlugin;

public class AdSky extends JavaPlugin {

	private PluginLogger logger;
	private PluginConfig config;

	@Override
	public void onEnable() {
		logger = new PluginLogger(this);
		try {
			logger.log("Loading config...", ChatColor.GOLD);
			config = new PluginConfig(this);
			config.load();

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

	public PluginLogger getAdSkyLogger() {
		return logger;
	}

	public PluginConfig getAdSkyConfig() {
		return config;
	}

}