package fr.skyost.adsky.sponge;

import com.eclipsesource.json.JsonObject;
import com.google.inject.Inject;
import fr.skyost.adsky.core.AbstractAdSkyApplication;
import fr.skyost.adsky.core.AdSkyConfiguration;
import fr.skyost.adsky.core.AdSkyLogger;
import fr.skyost.adsky.sponge.config.AdSkySpongeConfiguration;
import fr.skyost.adsky.sponge.config.SpongeConfig;
import fr.skyost.adsky.sponge.logger.AdSkySpongeLogger;
import fr.skyost.adsky.sponge.scheduler.AdSkySpongeTaskScheduler;
import fr.skyost.adsky.sponge.utils.OreUpdater;
import org.slf4j.Logger;
import org.spongepowered.api.config.DefaultConfig;
import org.spongepowered.api.event.Listener;
import org.spongepowered.api.event.game.state.GamePreInitializationEvent;
import org.spongepowered.api.plugin.Dependency;
import org.spongepowered.api.plugin.Plugin;

import java.nio.file.Path;

@Plugin(id = "adsky-sponge",
	name = "AdSky",
	description = "Monetize your Minecraft server !",
	version = "0.1.2",
	url = "https://github.com/Skyost/AdSky",
	authors = "Skyost",
	dependencies = {@Dependency(id = "spongeapi", version = "7.1.0")}
)
public class AdSkySpongePlugin extends AbstractAdSkyApplication {

	@Inject
	@DefaultConfig(sharedRoot = false)
	private Path dataFolder;

	@Inject
	private Logger logger;

	private final AdSkySpongeTaskScheduler adSkySpongeTaskScheduler = new AdSkySpongeTaskScheduler(this);
	private AdSkySpongeConfiguration adSkySpongeConfiguration;
	private AdSkySpongeLogger adskySpongeLogger;

	@Listener
	public final void onGamePreInitialize(final GamePreInitializationEvent event) {
		try {
			adskySpongeLogger = new AdSkySpongeLogger(logger);

			adSkySpongeConfiguration = new AdSkySpongeConfiguration(dataFolder.resolve("config.conf"));
			adSkySpongeConfiguration.load();

			if(adSkySpongeConfiguration.enableUpdater) {
				new OreUpdater(logger).start();
			}

			this.startMainTask();
		}
		catch(final SpongeConfig.InvalidConfigurationException ex) {
			logger.error("Error loading config :", ex);
		}
	}

	@Override
	public final AdSkyLogger getLogger() {
		return adskySpongeLogger;
	}

	@Override
	public final AdSkyConfiguration getConfiguration() {
		return adSkySpongeConfiguration;
	}

	@Override
	public final String getServerPluginKey() {
		return adSkySpongeConfiguration.serverPluginKey;
	}

	@Override
	public final AdSkySpongeAd createAdFromJSON(final JsonObject jsonObject) {
		return AdSkySpongeAd.fromJSON(adSkySpongeConfiguration, jsonObject);
	}

	@Override
	public final AdSkySpongeTaskScheduler getTaskScheduler() {
		return adSkySpongeTaskScheduler;
	}

}
