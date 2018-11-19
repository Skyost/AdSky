package fr.skyost.adsky.sponge;

import com.eclipsesource.json.JsonObject;
import com.google.inject.Inject;

import org.slf4j.Logger;
import org.spongepowered.api.Game;
import org.spongepowered.api.Sponge;
import org.spongepowered.api.asset.Asset;
import org.spongepowered.api.config.DefaultConfig;
import org.spongepowered.api.event.Listener;
import org.spongepowered.api.event.game.GameReloadEvent;
import org.spongepowered.api.event.game.state.GamePreInitializationEvent;
import org.spongepowered.api.plugin.Plugin;

import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.util.Optional;

import fr.skyost.adsky.core.AbstractAdSkyApplication;
import fr.skyost.adsky.core.AdSkyConfiguration;
import fr.skyost.adsky.core.AdSkyLogger;
import fr.skyost.adsky.sponge.config.AdSkySpongeConfiguration;
import fr.skyost.adsky.sponge.logger.AdSkySpongeLogger;
import fr.skyost.adsky.sponge.scheduler.AdSkySpongeTaskScheduler;
import fr.skyost.adsky.sponge.utils.OreUpdater;
import ninja.leaping.configurate.ConfigurationNode;
import ninja.leaping.configurate.commented.CommentedConfigurationNode;
import ninja.leaping.configurate.hocon.HoconConfigurationLoader;
import ninja.leaping.configurate.loader.ConfigurationLoader;
import ninja.leaping.configurate.objectmapping.ObjectMappingException;

@Plugin(id = "adsky-sponge", name = "AdSky", description = "Monetize your Minecraft server !", version = "0.1.2", url = "https://github.com/Skyost/AdSky", authors = "Skyost")
public class AdSkySpongePlugin extends AbstractAdSkyApplication {

	@Inject
	@DefaultConfig(sharedRoot = true)
	private Path configPath;

	@Inject
	@DefaultConfig(sharedRoot = true)
	private ConfigurationLoader<CommentedConfigurationNode> loader;

	@Inject
	private Logger logger;

	@Inject
	private Game game;

	private final AdSkySpongeTaskScheduler adSkySpongeTaskScheduler = new AdSkySpongeTaskScheduler(this);
	private AdSkySpongeConfiguration adSkySpongeConfiguration;
	private AdSkySpongeLogger adskySpongeLogger;

	@Listener
	public final void onGamePreInitialize(final GamePreInitializationEvent event) {
		try {
			final Optional<Asset> optionalAsset = game.getAssetManager().getAsset(this, "default.conf");
			if(!Files.exists(configPath) && optionalAsset.isPresent()) {
				optionalAsset.get().copyToFile(configPath);
			}

			final ConfigurationNode root = loader.load();
			/*if(root.getNode("version").getInt() < 2) {
				root.mergeValuesFrom(loadDefault());
				root.getNode("version").setValue(2);
				loader.save(root);
			}*/

			adSkySpongeConfiguration = root.getValue(AdSkySpongeConfiguration.TYPE);
			adskySpongeLogger = new AdSkySpongeLogger(logger);

			if(adSkySpongeConfiguration.enable.updater) {
				new OreUpdater(logger).start();
			}

			this.startMainTask();
		}
		catch(final IOException ex) {
			logger.error("Error loading config :", ex);
			mapDefault();
		}
		catch(final ObjectMappingException ex) {
			logger.error("Invalid config file :", ex);
			mapDefault();
		}
	}

	@Listener
	public void onReload(final GameReloadEvent event) {
		try {
			adSkySpongeConfiguration = loader.load().getValue(AdSkySpongeConfiguration.TYPE);
		}
		catch(final IOException ex) {
			logger.error("Error loading config :", ex);
		}
		catch(final ObjectMappingException ex) {
			logger.error("Invalid config file :", ex);
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
		return adSkySpongeConfiguration.server.pluginKey;
	}

	@Override
	public final AdSkySpongeAd createAdFromJSON(final JsonObject jsonObject) {
		return AdSkySpongeAd.fromJSON(adSkySpongeConfiguration, jsonObject);
	}

	@Override
	public final AdSkySpongeTaskScheduler getTaskScheduler() {
		return adSkySpongeTaskScheduler;
	}

	private void mapDefault() {
		try {
			adSkySpongeConfiguration = loadDefault().getValue(AdSkySpongeConfiguration.TYPE);
		}
		catch(final IOException | ObjectMappingException ex) {
			logger.error("Could not load the embedded default config :", ex);
			logger.error("Disabling plugin...");
			game.getEventManager().unregisterPluginListeners(this);
		}
	}

	private ConfigurationNode loadDefault() throws IOException {
		return HoconConfigurationLoader.builder().setURL(Sponge.getAssetManager().getAsset(this, "default.conf").get().getUrl()).build().load(loader.getDefaultOptions());
	}

}