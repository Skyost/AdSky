package fr.skyost.adsky.tasks;

import fr.skyost.adsky.Ad;
import fr.skyost.adsky.AdSky;
import fr.skyost.adsky.config.PluginConfig;
import fr.skyost.adsky.logger.PluginLogger;
import fr.skyost.adsky.utils.Utils;
import org.bukkit.ChatColor;
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

public class BroadcastAdsTask implements Runnable {

	private static final String AD_DELETE_EXPIRED_URL = "%sapi/plugin/deleted_expired.php";
	private static final String AD_REQUEST_URL = "%sapi/plugin/today.php";

	private Scheduler scheduler;
	private final AdSky plugin;

	public BroadcastAdsTask(final AdSky plugin) {
		this.plugin = plugin;
	}

	@Override
	public void run() {
		final PluginLogger logger = plugin.getAdSkyLogger();
		final PluginConfig config = plugin.getAdSkyConfig();

		if(scheduler == null) {
			if(!config.serverEventScheduled) {
				logger.log("Deleting expired ads...", ChatColor.GOLD);
				if(deleteExpiredAds()) {
					logger.success("Success !");
				}
				else {
					logger.error("Error while deleting expired ads.");
				}
			}

			logger.log("Getting ads...", ChatColor.GOLD);
			final HashSet<Ad> ads = requestAds();

			if(ads != null) {
				logger.success("Found " + ads.size() + " ad(s) to broadcast today.");
				scheduler = new Scheduler(plugin, new ArrayList<>(ads));
				scheduler.schedule();
			}
		}
		else {
			logger.success("Broadcasting a random ad from list...");
			scheduler.broadcastRandomAd();

			if(!scheduler.hasRemaining()) {
				scheduler = null;
				System.out.println("Buy scheduler");
			}
			logger.success("Success !");
		}

		System.out.println(scheduler == null ? "Buy scheduler 2" : "");
		final Calendar nextSchedule = scheduler == null ? Utils.tomorrowMidnight() : scheduler.getNextSchedule();
		logger.success("Scheduled next ad broadcast (if available) on " + nextSchedule.getTime() + ".");

		final long delay = nextSchedule.getTimeInMillis() - System.currentTimeMillis();
		Executors.newScheduledThreadPool(1).schedule(this, delay < 0 ? 0 : delay, TimeUnit.MILLISECONDS);
	}

	private boolean deleteExpiredAds() {
		try {
			return httpPost(AD_DELETE_EXPIRED_URL).get("error") == null;
		}
		catch(final Exception ex) {
			ex.printStackTrace();
		}

		return false;
	}

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

	private JSONObject httpPost(final String requestUrl) throws IOException, ParseException {
		final PluginConfig config = plugin.getAdSkyConfig();
		final String parameters = "key=" + URLEncoder.encode(config.serverPluginKey, "UTF-8");

		final URL url = new URL(String.format(requestUrl, config.serverUrl));
		final HttpURLConnection connection = (HttpURLConnection)url.openConnection();
		connection.setRequestMethod("POST");
		connection.setDoOutput(true);

		final DataOutputStream writer = new DataOutputStream(connection.getOutputStream());
		writer.writeBytes(parameters);
		writer.flush();
		writer.close();

		if(connection.getResponseCode() != 200) {
			throw new IOException("Invalid response code.");
		}

		final BufferedReader input = new BufferedReader(new InputStreamReader(connection.getInputStream()));

		String line;
		final StringBuilder response = new StringBuilder();
		while((line = input.readLine()) != null) {
			response.append(line);
		}

		input.close();

		final JSONParser parser = new JSONParser();
		return (JSONObject)parser.parse(response.toString());
	}

}