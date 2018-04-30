package fr.skyost.adsky.core.tasks;

import com.eclipsesource.json.Json;
import com.eclipsesource.json.JsonObject;
import com.eclipsesource.json.JsonValue;
import fr.skyost.adsky.core.*;
import fr.skyost.adsky.core.objects.Ad;
import fr.skyost.adsky.core.objects.Scheduler;
import fr.skyost.adsky.core.utils.Utils;

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
 * A tasks that broadcasts ad on the server.
 */

public class BackgroundTask implements Runnable {

	/**
	 * The non formatted delete expired ads url.
	 */

	private static final String AD_DELETE_EXPIRED_URL = "%sapi/plugin/deleted-expired";

	/**
	 * The non formatted delete request ads url.
	 */

	private static final String AD_REQUEST_URL = "%sapi/plugin/today";

	/**
	 * The scheduler.
	 */

	private Scheduler scheduler;

	/**
	 * The application instance.
	 */

	private final AdSkyApplication app;

	/**
	 * Creates a new tasks instance.
	 *
	 * @param app The AdSky application.
	 */

	public BackgroundTask(final AdSkyApplication app) {
		this.app = app;
	}

	@Override
	public void run() {
		// Let's get required objects.
		final AdSkyLogger logger = app.getLogger();
		final AdSkyConfiguration config = app.getConfiguration();

		// If scheduler is null, it means this is the first time of the day that this tasks is running.
		if(scheduler == null) {

			// So let's delete expired ads if needed.
			if(config.shouldAutoDeleteAds()) {
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
				scheduler = new Scheduler(app, new ArrayList<>(ads));
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

		// And then let's reschedule the tasks.
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
			final JsonObject jsonResponse = httpPost(AD_REQUEST_URL);

			final JsonValue object = jsonResponse.get("object");
			final JsonValue error = jsonResponse.get("error");
			if(object.isNull() || !error.isNull()) {
				app.getLogger().error("Unable to get ads : \"" + (object.isNull() ? "Object is null" : error.asString()) + "\".");
				return null;
			}

			final HashSet<Ad> result = new HashSet<>();
			for(JsonValue ad : object.asArray()) {
				result.addAll(Arrays.asList(app.createAdFromJSON(ad.asObject()).multiply()));
			}

			return result;
		}
		catch(final Exception ex) {
			app.getLogger().error("Unable to request ads.");
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
	 */

	private JsonObject httpPost(final String requestUrl) throws IOException {
		// We build the parameters.
		final String parameters = "key=" + URLEncoder.encode(app.getServerPluginKey(), "UTF-8");

		// We get the URL.
		final URL url = new URL(String.format(requestUrl, app.getConfiguration().getServerURL()));
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
		return Json.parse(new InputStreamReader(connection.getInputStream())).asObject();
	}

}