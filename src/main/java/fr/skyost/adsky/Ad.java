package fr.skyost.adsky;

import com.udojava.evalex.Expression;
import fr.skyost.adsky.config.PluginConfig;
import fr.skyost.adsky.utils.Utils;
import org.bukkit.Bukkit;
import org.bukkit.entity.Player;
import org.json.simple.JSONObject;

import java.math.BigDecimal;
import java.math.RoundingMode;

public class Ad {

	public static final int TYPE_TITLE = 0;
	public static final int TYPE_CHAT = 1;

	private final String username;
	private final int type;
	private final String title;
	private final String message;
	private final int interval;
	private final long expiration;
	private final int duration;

	public Ad(final String username, final int type, final String title, final String message, final int interval, final long expiration, final int duration) {
		this.username = username;
		this.type = type;
		this.title = title;
		this.message = message;
		this.interval = interval;
		this.expiration = expiration;
		this.duration = duration;
	}

	public Ad(final Ad ad) {
		this(ad.username, ad.type, ad.title, ad.message, ad.interval, ad.expiration, ad.duration);
	}

	public final String getUsername() {
		return username;
	}

	public final int getType() {
		return type;
	}

	public final String getTitle() {
		return title;
	}

	public final String getMessage() {
		return message;
	}

	public final int getInterval() {
		return interval;
	}

	public final long getExpiration() {
		return expiration;
	}

	public final int getDuration() {
		return duration;
	}

	public final Ad[] multiply() {
		Ad[] array = new Ad[interval];
		array[0] = this;
		for(int i = 1; i < interval; i++) {
			array[i] = new Ad(this);
		}
		return array;
	}

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

	public static int getAdsPerHour(final PluginConfig config, final int preferredHour, final int hour, final int adsNumber) {
		final Expression expression = new Expression(config.adsDistributionFunction);
		final BigDecimal result = expression.with("h", new BigDecimal(preferredHour))
						   .with("x", new BigDecimal(hour))
						   .with("n", new BigDecimal(adsNumber))
						   .eval();

		return result.setScale(0, RoundingMode.UP).intValue();
	}

}