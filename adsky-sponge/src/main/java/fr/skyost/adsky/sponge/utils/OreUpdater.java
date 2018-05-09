package fr.skyost.adsky.sponge.utils;

import com.eclipsesource.json.Json;
import com.goebl.david.Webb;
import com.goebl.david.WebbException;
import org.slf4j.Logger;
import org.spongepowered.api.Sponge;
import org.spongepowered.api.plugin.PluginContainer;

import java.util.Optional;

public class OreUpdater extends Thread {

	private static final String PLUGIN_ID = "adsky-sponge";

	private final Logger logger;

	public OreUpdater(final Logger logger) {
		this.logger = logger;
	}

	@Override
	public final void run() {
		try {
			logger.info("Checking for updates...");

			final Optional<PluginContainer> optionalPluginContainer = Sponge.getPluginManager().getPlugin(PLUGIN_ID);
			if(!optionalPluginContainer.isPresent()) {
				throw new NullPointerException("No plugin found for \"" + PLUGIN_ID + "\".");
			}
			final Optional<String> optionalLocalVersion = optionalPluginContainer.get().getVersion();
			if(!optionalLocalVersion.isPresent()) {
				return;
			}

			final Webb webb = Webb.create();
			webb.setBaseUri("https://ore.spongepowered.org/api/v1");

			final String version = Json.parse(webb.get("/projects/" + PLUGIN_ID).ensureSuccess().asString().getBody()).asObject().get("recommended").asObject().get("name").asString();

			if(versionCompare(optionalLocalVersion.get(), version) >= 0) {
				logger.info("No update found.");
				return;
			}

			logger.info("Found an update !");
			logger.info("Head to \"https://ore.spongepowered.org/api/projects/" + PLUGIN_ID + "/versions/recommended/download\" to download " + version + "...");
		}
		catch(final Exception ex) {
			logger.error("Cannot check for updates :", ex);
		}
	}

	/**
	 * Compares two version strings.
	 *
	 * Use this instead of String.compareTo() for a non-lexicographical
	 * comparison that works for version strings. e.g. "1.10".compareTo("1.6").
	 *
	 * @note It does not work if "1.10" is supposed to be equal to "1.10.0".
	 *
	 * @param str1 a string of ordinal numbers separated by decimal points.
	 * @param str2 a string of ordinal numbers separated by decimal points.
	 * @return The result is a negative integer if str1 is _numerically_ less than str2.
	 *         The result is a positive integer if str1 is _numerically_ greater than str2.
	 *         The result is zero if the strings are _numerically_ equal.
	 *
	 * @author Alex Gitelman.
	 */

	public static int versionCompare(final String str1, final String str2) {
		final String[] vals1 = str1.split("\\.");
		final String[] vals2 = str2.split("\\.");
		int i = 0;
		// set index to first non-equal ordinal or length of shortest version string
		while(i < vals1.length && i < vals2.length && vals1[i].equals(vals2[i])) {
			i++;
		}
		// compare first non-equal ordinal number
		if(i < vals1.length && i < vals2.length) {
			int diff = Integer.valueOf(vals1[i]).compareTo(Integer.valueOf(vals2[i]));
			return Integer.signum(diff);
		}
		// the strings are equal or one string is a substring of the other
		// e.g. "1.2.3" = "1.2.3" or "1.2.3" < "1.2.3.4"
		return Integer.signum(vals1.length - vals2.length);
	}

}