package fr.skyost.adsky.sponge;

import com.eclipsesource.json.JsonObject;
import com.eclipsesource.json.JsonValue;
import fr.skyost.adsky.core.ad.AbstractAd;
import fr.skyost.adsky.core.utils.Utils;
import fr.skyost.adsky.sponge.config.AdSkySpongeConfiguration;
import org.spongepowered.api.Sponge;
import org.spongepowered.api.entity.living.player.Player;
import org.spongepowered.api.text.Text;
import org.spongepowered.api.text.serializer.TextSerializers;
import org.spongepowered.api.text.title.Title;
import org.spongepowered.api.world.World;

public class AdSkySpongeAd extends AbstractAd {

	/**
	 * The plugin config.
	 */

	private final AdSkySpongeConfiguration config;

	/**
	 * Creates a new AdSkySpongeAd instance.
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

	private AdSkySpongeAd(final AdSkySpongeConfiguration config, final String username, final int type, final String title, final String message, final int interval, final long expiration, final int duration) {
		super(username, type, title, message, interval, expiration, duration);

		this.config = config;
	}

	/**
	 * Allows to clone a AdSkySpongeAd.
	 *
	 * @param ad The AdSkySpongeAd to clone.
	 */

	private AdSkySpongeAd(final AdSkySpongeAd ad) {
		super(ad);

		this.config = ad.config;
	}
	
	@Override
	public void broadcast() {
		final Text deserializedTitle = TextSerializers.formattingCode('&').deserialize(this.getTitle());
		final Text deserializedMessage = TextSerializers.formattingCode('&').deserialize(this.getMessage());

		final Title title = Title
				.builder()
				.title(deserializedTitle)
				.subtitle(deserializedMessage)
				.stay(this.getDuration() * 20)
				.build();

		for(final World world : Sponge.getServer().getWorlds()) {
			if(config.ads.worldBlackList.contains(world.getName())) {
				continue;
			}

			for(final Player player : world.getPlayers()) {
				if(player.hasPermission("adsky.bypass")) {
					continue;
				}

				if(this.isTitleAd()) {
					player.sendTitle(title);
					continue;
				}

				player.sendMessage(deserializedTitle);
				player.sendMessage(deserializedMessage);
			}
		}
	}

	@Override
	public AdSkySpongeAd copy() {
		return new AdSkySpongeAd(this);
	}

	/**
	 * Creates an AdSkySpongeAd instance from a JSON object.
	 *
	 * @param config Plugin config.
	 * @param object The JSON object.
	 *
	 * @return The new AdSkySpongeAd instance.
	 */

	public static AdSkySpongeAd fromJSON(final AdSkySpongeConfiguration config, final JsonObject object) {
		final AdSkySpongeAd ad = new AdSkySpongeAd(config, "Skyost", TYPE_CHAT, "An ad", "This is an ad !", 1, Utils.tomorrowMidnight().getTimeInMillis(), 4);

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