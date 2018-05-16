package fr.skyost.adsky.sponge.config;

import com.google.common.collect.ImmutableList;
import com.google.common.reflect.TypeToken;
import fr.skyost.adsky.core.AdSkyConfiguration;
import ninja.leaping.configurate.objectmapping.Setting;
import ninja.leaping.configurate.objectmapping.serialize.ConfigSerializable;

import java.util.List;

/**
 * Default plugin configuration.
 */

@ConfigSerializable
public class AdSkySpongeConfiguration implements AdSkyConfiguration {

	public static final TypeToken<AdSkySpongeConfiguration> TYPE = TypeToken.of(AdSkySpongeConfiguration.class);

	@Setting
	public String version;

	@Setting
	public Enable enable;

	@Setting
	public Server server;

	@Setting
	public Ads ads;

	@ConfigSerializable
	public static class Enable {

		@Setting
		public boolean updater = true;

	}

	@ConfigSerializable
	public static class Server {

		@Setting
		public String url = "http://yourwebsite.com/adsky/";

		@Setting("plugin-key")
		public String pluginKey = "Paste your plugin key here.";

		@Setting("event-scheduled")
		public boolean eventScheduled = false;

	}

	@ConfigSerializable
	public static class Ads {

		@Setting("preferred-hour")
		public int preferredHour = 12;

		@Setting("distribution-function")
		public String distributionFunction = "(sqrt(n/2)/log10(n+2)) * e^(-((x-h)^2) / (2*log10(n+2)))";

		@Setting("world-blacklist")
		public List<String> worldBlackList = ImmutableList.of();

	}

	@Override
	public final String getServerURL() {
		return server.url;
	}

	@Override
	public final boolean shouldAutoDeleteAds() {
		return !server.eventScheduled;
	}

	@Override
	public final String getAdsDistributionFunction() {
		return ads.distributionFunction;
	}

	@Override
	public final int getAdsPreferredHour() {
		return ads.preferredHour;
	}

}