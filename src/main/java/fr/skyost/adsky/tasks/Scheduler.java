package fr.skyost.adsky.tasks;

import com.google.common.collect.ArrayListMultimap;
import com.google.common.collect.Multimap;
import fr.skyost.adsky.Ad;
import fr.skyost.adsky.AdSky;
import fr.skyost.adsky.config.PluginConfig;
import fr.skyost.adsky.utils.Utils;

import java.util.Calendar;
import java.util.Collection;
import java.util.List;
import java.util.Random;

public class Scheduler {

	private final List<Ad> notScheduled;
	private final Multimap<Integer, Ad> scheduled = ArrayListMultimap.create();

	private final PluginConfig config;

	public Scheduler(final AdSky plugin, final List<Ad> ads) {
		this.config = plugin.getAdSkyConfig();
		notScheduled = ads;
	}

	public final void schedule() {
		scheduled.clear();

		final Random random = new Random();
		final int currentHour = Calendar.getInstance().get(Calendar.HOUR_OF_DAY);

		while(!notScheduled.isEmpty()) {
			final int adsNumber = notScheduled.size();

			int preferredHour = config.adsPreferredHour;
			if(currentHour >= preferredHour) {
				preferredHour = currentHour;

				if(Calendar.getInstance().get(Calendar.MINUTE) >= 50) {
					preferredHour++;
				}
			}

			boolean minus = true;
			for(int hourMinus = preferredHour, hourPlus = preferredHour; (hourMinus > currentHour || hourPlus <= 23) && !notScheduled.isEmpty(); minus = !minus) {
				final int hour = minus ? hourMinus : hourPlus;
				final int hourAdsNumber = Ad.getAdsPerHour(config, preferredHour, hour, adsNumber);

				for(int i = 0; i < hourAdsNumber; i++) {
					if(notScheduled.isEmpty()) {
						break;
					}

					final Ad ad = notScheduled.get(random.nextInt(notScheduled.size()));
					scheduled.put(hour, ad);
					notScheduled.remove(ad);
				}

				if(minus) {
					hourMinus--;
				}
				else {
					hourPlus++;
				}

				if(hourMinus <= currentHour) {
					minus = true;
					hourPlus++;
				}
				if(hourPlus > 23) {
					minus = false;
					hourMinus--;
				}
			}
		}
	}

	public final boolean hasRemaining() {
		return !scheduled.isEmpty();
	}

	public final void broadcastRandomAd() {
		if(!hasRemaining()) {
			return;
		}

		final int hour = Calendar.getInstance().get(Calendar.HOUR_OF_DAY);

		final List<Ad> ads = (List<Ad>)scheduled.get(hour);
		final Ad ad = ads.get(new Random().nextInt(ads.size()));
		ad.broadcast(config);

		scheduled.remove(hour, ad);
	}

	public final Calendar getNextSchedule() {
		if(scheduled.isEmpty()) {
			return Utils.tomorrowMidnight();
		}

		int hour = Calendar.getInstance().get(Calendar.HOUR_OF_DAY);
		Collection<Ad> ads = null;
		for(; ads == null || ads.isEmpty(); hour++) {
			ads = scheduled.get(hour);
		}
		hour--;

		Calendar calendar = Calendar.getInstance();
		calendar.set(Calendar.MILLISECOND, 0);
		calendar.set(Calendar.SECOND, 0);
		if(hour == calendar.get(Calendar.HOUR_OF_DAY)) {
			calendar.add(Calendar.MINUTE, ((60 - calendar.get(Calendar.MINUTE)) / ads.size()) - 1);
		}
		else {
			calendar.set(Calendar.MINUTE, (60 / ads.size()) - 1);
			calendar.set(Calendar.HOUR_OF_DAY, hour);
		}

		if(calendar.before(Calendar.getInstance())) {
			calendar = Calendar.getInstance();
		}

		return calendar;
	}

}