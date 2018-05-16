package fr.skyost.adsky.core.tasks;

import com.eclipsesource.json.Json;
import com.eclipsesource.json.JsonObject;
import com.eclipsesource.json.JsonValue;
import fr.skyost.adsky.core.*;
import fr.skyost.adsky.core.ad.AbstractAd;
import fr.skyost.adsky.core.ad.AdScheduler;
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

/**
 * A tasks that broadcasts ad on the server.
 */

public class BackgroundTask implements Runnable {

	/**
	 * The non formatted delete expired ads url.
	 */

	private static final String AD_DELETE_EXPIRED_URL = "%sapi/v1/plugin/delete-expired";

	/**
	 * The non formatted delete request ads url.
	 */

	private static final String AD_REQUEST_URL = "%sapi/v1/plugin/today";

	/**
	 * The adScheduler.
	 */

	private AdScheduler adScheduler;

	/**
	 * The application instance.
	 */

	private final AbstractAdSkyApplication app;

	/**
	 * The language.
	 */

	private final AdSkyLanguage lang;

	/**
	 * Creates a new tasks instance.
	 *
	 * @param app The AdSky application.
	 */

	public BackgroundTask(final AbstractAdSkyApplication app) {
		this.app = app;
		this.lang = app.getLanguage();
	}

	@Override
	public void run() {
		final AdSkyLogger logger = app.getLogger();

		try {
			// Let's get required ad.
			final AdSkyConfiguration config = app.getConfiguration();

			// If adScheduler is null, it means this is the first time of the day that this tasks is running.
			if(adScheduler == null) {
				// So let's delete expired ads if needed.
				if(config.shouldAutoDeleteAds()) {
					deleteExpiredAds();
				}

				// And let's get ads of the day and schedule them.
				logger.message(lang.gettingAds());
				final HashSet<AbstractAd> ads = requestAds();

				if(ads != null) {
					logger.success(lang.foundAds(ads.size()));
					adScheduler = new AdScheduler(app, new ArrayList<>(ads));
					adScheduler.schedule();
				}
			}
			else {
				// Here we are going to broadcast a random ad from the list.
				logger.success(lang.broadcastingRandomAd());
				adScheduler.broadcastRandomAd();

				if(!adScheduler.hasRemaining()) {
					adScheduler = null;
				}
				logger.success(lang.success());
			}


			// And then let's reschedule the tasks.
			final Calendar nextSchedule = adScheduler == null ? Utils.tomorrowMidnight() : adScheduler.getNextSchedule();
			logger.success(lang.scheduledAt(nextSchedule));

			final long delay = nextSchedule.getTimeInMillis() - System.currentTimeMillis();
			app.getTaskScheduler().schedule(this, delay <= 0 ? 5000L : delay);
		}
		catch(final Exception ex) {
			logger.error("An error occurred while running the background task :", ex);
		}
	}

	/**
	 * Sends a delete expired ads request.
	 */

	private void deleteExpiredAds() {
		final AdSkyLogger logger = app.getLogger();
		try {
			logger.message(lang.deletingExpiredAds());

			final JsonValue error = httpPost(AD_DELETE_EXPIRED_URL).get("error");
			if(!error.isNull()) {
				logger.error(lang.unableDeleteExpiredAds() + " \"" + error.asString() + "\".");
				return;
			}
			logger.success(lang.success());
		}
		catch(final Exception ex) {
			logger.error(lang.unableDeleteExpiredAds(), ex);
		}
	}

	/**
	 * Request ads on the server.
	 *
	 * @return A Set containing today's ads.
	 */

	private HashSet<AbstractAd> requestAds() {
		try {
			final JsonObject jsonResponse = httpPost(AD_REQUEST_URL);

			final JsonValue object = jsonResponse.get("object");
			final JsonValue error = jsonResponse.get("error");
			if(object.isNull() || !error.isNull()) {
				app.getLogger().error(lang.unableRequestAds() + " \"" + (error.isNull() ? "Object is null" : error.asString()) + "\".");
				return null;
			}

			final HashSet<AbstractAd> result = new HashSet<>();
			for(JsonValue ad : object.asArray()) {
				result.addAll(Arrays.asList(app.createAdFromJSON(ad.asObject()).multiply()));
			}

			return result;
		}
		catch(final Exception ex) {
			app.getLogger().error(lang.unableRequestAds(), ex);
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
			throw new IOException(lang.invalidResponseCode(connection.getResponseCode()));
		}

		// We can now build the response.
		return Json.parse(new InputStreamReader(connection.getInputStream())).asObject();
	}

}