package fr.skyost.adsky.tasks;

import fr.skyost.adsky.Ad;
import fr.skyost.adsky.AdSky;
import fr.skyost.adsky.Scheduler;
import fr.skyost.adsky.config.PluginConfig;
import fr.skyost.adsky.logger.PluginLogger;
import fr.skyost.adsky.utils.Utils;
import org.json.simple.JSONArray;
import org.json.simple.JSONObject;
import org.json.simple.parser.JSONParser;
import org.json.simple.parser.ParseException;

import java.io.BufferedReader;
import java.io.DataOutputStream;
import java.io.IOException;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.Calendar;
import java.util.HashSet;
import java.util.concurrent.Executors;
import java.util.concurrent.TimeUnit;

/**
 * A task that broadcasts ad on the server.
 */

public class BroadcastAdsTask implements Runnable {

	/**
	 * The non formatted delete expired ads url.
	 */

	private static final String AD_DELETE_EXPIRED_URL = "%sapi/plugin/deleted_expired.php";

	/**
	 * The non formatted delete request ads url.
	 */

	private static final String AD_REQUEST_URL = "%sapi/plugin/today.php";

	/**
	 * The scheduler.
	 */

	private Scheduler scheduler;

	/**
	 * The plugin instance.
	 */

	private final AdSky plugin;

	/**
	 * Creates a new task instance.
	 *
	 * @param plugin The AdSky plugin.
	 */

	public BroadcastAdsTask(final AdSky plugin) {
		this.plugin = plugin;
	}

	@Override
	public void run() {
		// Let's get required objects.
		final PluginLogger logger = plugin.getAdSkyLogger();
		final PluginConfig config = plugin.getAdSkyConfig();

		// If scheduler is null, it means this is the first time of the day that this task is running.
		if(scheduler == null) {

			// So let's delete expired ads if needed.
			if(!config.serverEventScheduled) {
				logger.message("Deleting expired ads...");
				if(deleteExpiredAds()) {
					logger.success("Success !");
				}
				else {
					logger.error("Error while deleting expired ads.");
				}
			}

			// And let's get ads of the day and schedule them.
			logger.message("Getting ads...");
			final HashSet<Ad> ads = requestAds();

			if(ads != null) {
				logger.success("Found " + ads.size() + " ad(s) to broadcast today.");
				scheduler = new Scheduler(plugin, new ArrayList<>(ads));
				scheduler.schedule();
			}
		}
		else {
			// Here we are going to broadcast a random ad from the list.
			logger.success("Broadcasting a random ad from list...");
			scheduler.broadcastRandomAd();

			if(!scheduler.hasRemaining()) {
				scheduler = null;
			}
			logger.success("Success !");
		}

		// And then let's reschedule the task.
		final Calendar nextSchedule = scheduler == null ? Utils.tomorrowMidnight() : scheduler.getNextSchedule();
		logger.success("Scheduled next ad broadcast (if available) on " + nextSchedule.getTime() + ".");

		final long delay = nextSchedule.getTimeInMillis() - System.currentTimeMillis();
		Executors.newScheduledThreadPool(1).schedule(this, delay < 0 ? 0 : delay, TimeUnit.MILLISECONDS);
	}

	/**
	 * Sends a delete expired ads request.
	 *
	 * @return A boolean indicating the success.
	 */

	private boolean deleteExpiredAds() {
		try {
			return httpPost(AD_DELETE_EXPIRED_URL).get("error") == null;
		}
		catch(final Exception ex) {
			ex.printStackTrace();
		}

		return false;
	}

	/**
	 * Request ads on the server.
	 *
	 * @return A Set containing today's ads.
	 */

	private HashSet<Ad> requestAds() {
		try {
			final JSONObject jsonResponse = httpPost(AD_REQUEST_URL);
			final JSONArray object = (JSONArray)jsonResponse.get("object");
			final String error = (String)jsonResponse.get("error");
			if(error != null || object == null) {
				plugin.getAdSkyLogger().error("Unable to get ads : \"" + error + "\".");
				return null;
			}

			final HashSet<Ad> result = new HashSet<>();
			for(Object ad : object) {
				result.addAll(Arrays.asList(Ad.fromJSON((JSONObject)ad).multiply()));
			}

			return result;
		}
		catch(final Exception ex) {
			plugin.getAdSkyLogger().error("Unable to request ads.");
			ex.printStackTrace();
		}

		return null;
	}

	/**
	 * Sends a HTTP POST request.
	 *
	 * @param requestUrl The request URL.
	 *
	 * @return The JSON Object if possible.
	 *
	 * @throws IOException If an I/O exception occurs.
	 * @throws ParseException If a parse exception occurs.
	 */

	private JSONObject httpPost(final String requestUrl) throws IOException, ParseException {
		// We build the parameters.
		final PluginConfig config = plugin.getAdSkyConfig();
		final String parameters = "key=" + URLEncoder.encode(config.serverPluginKey, "UTF-8");

		// We get the URL.
		final URL url = new URL(String.format(requestUrl, config.serverUrl));
		final HttpURLConnection connection = (HttpURLConnection)url.openConnection();
		connection.setRequestMethod("POST");
		connection.setDoOutput(true);

		// Then we send everything.
		final DataOutputStream writer = new DataOutputStream(connection.getOutputStream());
		writer.writeBytes(parameters);
		writer.flush();
		writer.close();

		// If the response code is not 200, we throw an error.
		if(connection.getResponseCode() != 200) {
			throw new IOException("Invalid response code.");
		}

		// We can now build the response.
		final BufferedReader input = new BufferedReader(new InputStreamReader(connection.getInputStream()));

		String line;
		final StringBuilder response = new StringBuilder();
		while((line = input.readLine()) != null) {
			response.append(line);
		}

		input.close();

		// And we return the parsed response.
		final JSONParser parser = new JSONParser();
		return (JSONObject)parser.parse(response.toString());
	}

}