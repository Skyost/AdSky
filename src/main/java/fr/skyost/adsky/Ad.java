package fr.skyost.adsky;

import com.udojava.evalex.Expression;
import fr.skyost.adsky.config.PluginConfig;
import fr.skyost.adsky.utils.Utils;
import org.bukkit.Bukkit;
import org.bukkit.entity.Player;
import org.json.simple.JSONObject;

import java.math.BigDecimal;
import java.math.RoundingMode;

/**
 * Represents an AdSky Ad.
 */

public class Ad {

	/**
	 * The Title ad type.
	 */

	public static final int TYPE_TITLE = 0;

	/**
	 * The Chat ad type.
	 */

	public static final int TYPE_CHAT = 1;

	/**
	 * Ad's username.
	 */

	private final String username;

	/**
	 * Ad's type.
	 */

	private final int type;

	/**
	 * Ad's title.
	 */

	private final String title;

	/**
	 * Ad's message.
	 */

	private final String message;

	/**
	 * Ad's interval.
	 */

	private final int interval;

	/**
	 * Ad's expiration.
	 */

	private final long expiration;

	/**
	 * Ad's duration.
	 */

	private final int duration;

	/**
	 * Creates a new Ad instance.
	 *
	 * @param username The username.
	 * @param type The type (see Ad constants).
	 * @param title The title.
	 * @param message The message.
	 * @param interval The interval.
	 * @param expiration The expiration.
	 * @param duration The duration.
	 */

	public Ad(final String username, final int type, final String title, final String message, final int interval, final long expiration, final int duration) {
		this.username = username;
		this.type = type;
		this.title = title;
		this.message = message;
		this.interval = interval;
		this.expiration = expiration;
		this.duration = duration;
	}

	/**
	 * Allows to clone an Ad.
	 *
	 * @param ad The Ad to clone.
	 */

	public Ad(final Ad ad) {
		this(ad.username, ad.type, ad.title, ad.message, ad.interval, ad.expiration, ad.duration);
	}

	/**
	 * Gets the username.
	 *
	 * @return The username.
	 */

	public final String getUsername() {
		return username;
	}

	/**
	 * Gets the type (see Ad constants).
	 *
	 * @return The type.
	 */

	public final int getType() {
		return type;
	}

	/**
	 * Gets the title.
	 *
	 * @return The title.
	 */

	public final String getTitle() {
		return title;
	}

	/**
	 * Gets the message.
	 *
	 * @return The message.
	 */

	public final String getMessage() {
		return message;
	}

	/**
	 * Gets the interval.
	 *
	 * @return The interval.
	 */

	public final int getInterval() {
		return interval;
	}

	/**
	 * Gets the expiration.
	 *
	 * @return The expiration.
	 */

	public final long getExpiration() {
		return expiration;
	}

	/**
	 * Gets the duration.
	 *
	 * @return The duration.
	 */

	public final int getDuration() {
		return duration;
	}

	/**
	 * Multiplies this ad according to the broadcast interval.
	 *
	 * @return An array containing all ads.
	 */

	public final Ad[] multiply() {
		Ad[] array = new Ad[interval];
		array[0] = this;
		for(int i = 1; i < interval; i++) {
			array[i] = new Ad(this);
		}
		return array;
	}

	/**
	 * Broadcasts this ad.
	 *
	 * @param config The plugin configuration.
	 */

	public final void broadcast(final PluginConfig config) {
		if(type == TYPE_TITLE) {
			int stay = duration * 20;
			for(final Player player : Bukkit.getOnlinePlayers()) {
				if(player.hasPermission("adsky.bypass") || config.adsWorldBlackList.contains(player.getWorld().getName())) {
					continue;
				}

				player.sendTitle(title, message, 10, stay, 20);
			}
			return;
		}

		final String[] message = new String[]{this.title, this.message};
		for(final Player player : Bukkit.getOnlinePlayers()) {
			if(player.hasPermission("adsky.bypass") || config.adsWorldBlackList.contains(player.getWorld().getName())) {
				continue;
			}

			player.sendMessage(message);
		}
	}

	/**
	 * Creates an Ad instance from a JSON object.
	 *
	 * @param object The JSON object.
	 *
	 * @return The new Ad instance.
	 */

	public static Ad fromJSON(final JSONObject object) {
		String username = (String)object.get("username");
		if(username == null || username.isEmpty()) {
			username = "Skyost";
		}

		Long type = (Long)object.get("type");
		if(type == null || (type != TYPE_TITLE && type != TYPE_CHAT)) {
			type = (long)TYPE_CHAT;
		}

		String title = (String)object.get("title");
		if(title == null || title.isEmpty()) {
			title = "An ad";
		}

		String message = (String)object.get("message");
		if(message == null || message.isEmpty()) {
			message = "This is an ad !";
		}

		Long interval = (Long)object.get("interval");
		if(interval == null || interval < 1) {
			interval = 1L;
		}

		Long expiration = (Long)object.get("expiration");
		if(expiration == null || expiration < System.currentTimeMillis()) {
			expiration = Utils.tomorrowMidnight().getTimeInMillis();
		}

		Long duration = (Long)object.get("duration");
		if(duration == null || (duration < 0 && type != TYPE_CHAT)) {
			duration = 4L;
		}

		return new Ad(username, type.intValue(), title, message, interval.intValue(), expiration, duration.intValue());
	}

	/**
	 * Evaluates the distribution function at the given hour.
	 *
	 * @param config The plugin configuration.
	 * @param preferredHour The preferred hour.
	 * @param hour The hour.
	 * @param adsNumber Today's ads number.
	 *
	 * @return The number of ads to broadcast at the given hour.
	 */

	public static int getAdsPerHour(final PluginConfig config, final int preferredHour, final int hour, final int adsNumber) {
		final Expression expression = new Expression(config.adsDistributionFunction);
		final BigDecimal result = expression.with("h", new BigDecimal(preferredHour))
						   .with("x", new BigDecimal(hour))
						   .with("n", new BigDecimal(adsNumber))
						   .eval();

		return result.setScale(0, RoundingMode.UP).intValue();
	}

}