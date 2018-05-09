package fr.skyost.adsky.core;

import com.eclipsesource.json.JsonObject;
import fr.skyost.adsky.core.ad.Ad;
import fr.skyost.adsky.core.lang.AdSkyEnglishLanguage;
import fr.skyost.adsky.core.scheduler.DefaultAdSkyTaskScheduler;
import fr.skyost.adsky.core.tasks.BackgroundTask;

/**
 * Represents an abstract AdSky application.
 */

public abstract class AdSkyApplication {

	/**
	 * Gets a logger instance.
	 *
	 * @return A logger instance.
	 */

	public abstract AdSkyLogger getLogger();

	/**
	 * Gets a configuration instance.
	 *
	 * @return A configuration instance.
	 */

	public abstract AdSkyConfiguration getConfiguration();

	/**
	 * Gets a AdSky task scheduler.
	 *
	 * @return A AdSky task scheduler.
	 */

	public AdSkyTaskScheduler getTaskScheduler() {
		return new DefaultAdSkyTaskScheduler();
	}

	/**
	 * Gets the AdSky language.
	 *
	 * @return The AdSky language.
	 */

	public AdSkyLanguage getLanguage() {
		return new AdSkyEnglishLanguage();
	}

	/**
	 * Gets the plugin server key.
	 *
	 * @return The plugin server key.
	 */

	public abstract String getServerPluginKey();

	/**
	 * Creates an Ad from a JSON String.
	 *
	 * @param jsonObject The JSON String (as Object).
	 *
	 * @return The Ad.
	 */

	public abstract Ad createAdFromJSON(final JsonObject jsonObject);

	/**
	 * Starts the main task.
	 */

	public void startMainTask() {
		new Thread(new BackgroundTask(this)).start();
	}

}