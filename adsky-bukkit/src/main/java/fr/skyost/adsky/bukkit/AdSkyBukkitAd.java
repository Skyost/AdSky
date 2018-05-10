package fr.skyost.adsky.bukkit;

import com.eclipsesource.json.JsonObject;
import com.eclipsesource.json.JsonValue;
import fr.skyost.adsky.bukkit.config.AdSkyBukkitConfiguration;
import fr.skyost.adsky.core.ad.AbstractAd;
import fr.skyost.adsky.core.utils.Utils;
import org.bukkit.Bukkit;
import org.bukkit.entity.Player;

/**
 * Bukkit implementation of Ad.
 */

public class AdSkyBukkitAd extends AbstractAd {

	/**
	 * The plugin config.
	 */

	private final AdSkyBukkitConfiguration config;

	/**
	 * Creates a new AdSkyBukkitAd instance.
	 *
	 * @param config Plugin config.
	 * @param username The username.
	 * @param type The type (see Ad constants).
	 * @param title The title.
	 * @param message The message.
	 * @param interval The interval.
	 * @param expiration The expiration.
	 * @param duration The duration.
	 */

	private AdSkyBukkitAd(final AdSkyBukkitConfiguration config, final String username, final int type, final String title, final String message, final int interval, final long expiration, final int duration) {
		super(username, type, title, message, interval, expiration, duration);

		this.config = config;
	}

	/**
	 * Allows to clone a AdSkyBukkitAd.
	 *
	 * @param ad The AdSkyBukkitAd to clone.
	 */

	private AdSkyBukkitAd(final AdSkyBukkitAd ad) {
		super(ad);

		this.config = ad.config;
	}

	@Override
	public void broadcast() {
		if(this.isTitleAd()) {
			final int stay = this.getDuration() * 20;
			for(final Player player : Bukkit.getOnlinePlayers()) {
				if(player.hasPermission("adsky.bypass") || config.adsWorldBlackList.contains(player.getWorld().getName())) {
					continue;
				}

				player.sendTitle(this.getTitle(), this.getMessage(), 10, stay, 20);
			}
			return;
		}

		final String[] message = new String[]{this.getTitle(), this.getMessage()};
		for(final Player player : Bukkit.getOnlinePlayers()) {
			if(player.hasPermission("adsky.bypass") || config.adsWorldBlackList.contains(player.getWorld().getName())) {
				continue;
			}

			player.sendMessage(message);
		}
	}

	@Override
	public AbstractAd[] multiply() {
		final int interval = this.getInterval();

		AbstractAd[] array = new AbstractAd[interval];
		array[0] = this;
		for(int i = 1; i < interval; i++) {
			array[i] = new AdSkyBukkitAd(this);
		}

		return array;
	}

	/**
	 * Creates an AdSkyBukkitAd instance from a JSON object.
	 *
	 * @param config Plugin config.
	 * @param object The JSON object.
	 *
	 * @return The new AdSkyBukkitAd instance.
	 */

	public static AdSkyBukkitAd fromJSON(final AdSkyBukkitConfiguration config, final JsonObject object) {
		final AdSkyBukkitAd ad = new AdSkyBukkitAd(config, "Skyost", TYPE_CHAT, "An ad", "This is an ad !", 1, Utils.tomorrowMidnight().getTimeInMillis(), 4);

		JsonValue username = object.get("username");
		if(username.isString() && !username.asString().isEmpty()) {
			ad.setUsername(username.asString());
		}

		JsonValue type = object.get("type");
		if(type.isNumber() && (type.asInt() == TYPE_TITLE || type.asInt() == TYPE_CHAT)) {
			ad.setType(type.asInt());
		}

		JsonValue title = object.get("title");
		if(title.isString() && !title.asString().isEmpty()) {
			ad.setTitle(title.asString());
		}

		JsonValue message = object.get("message");
		if(message.isString() && !message.asString().isEmpty()) {
			ad.setMessage(message.asString());
		}

		JsonValue interval = object.get("interval");
		if(interval.isNumber() && interval.asInt() >= 1) {
			ad.setInterval(interval.asInt());
		}

		JsonValue expiration = object.get("expiration");
		if(expiration.isNumber() && expiration.asLong() >= System.currentTimeMillis()) {
			ad.setExpiration(expiration.asLong());
		}

		JsonValue duration = object.get("duration");
		if(duration.isNumber() && duration.asInt() >= 0 && ad.isTitleAd()) {
			ad.setDuration(duration.asInt());
		}

		return ad;
	}

}
