package fr.skyost.adsky;

import com.google.common.collect.ArrayListMultimap;
import com.google.common.collect.Multimap;
import fr.skyost.adsky.config.PluginConfig;
import fr.skyost.adsky.utils.Utils;

import java.util.Calendar;
import java.util.Collection;
import java.util.List;
import java.util.Random;

/**
 * A class that is used to broadcast ads.
 */

public class Scheduler {

	/**
	 * All ads that have not been scheduled yet.
	 */

	private final List<Ad> notScheduled;

	/**
	 * All scheduled ads.
	 */

	private final Multimap<Integer, Ad> scheduled = ArrayListMultimap.create();

	/**
	 * The plugin's configuration.
	 */

	private final PluginConfig config;

	/**
	 * Creates a new scheduler.
	 *
	 * @param plugin The plugin instance.
	 * @param ads Ads to schedule.
	 */

	public Scheduler(final AdSky plugin, final List<Ad> ads) {
		this.config = plugin.getAdSkyConfig();
		notScheduled = ads;
	}

	/**
	 * Schedules all ads (that have not been scheduled yet).
	 */

	public final void schedule() {
		// We clear all scheduled ads.
		scheduled.clear();

		// We gets required information before entering into the loop.
		final Random random = new Random();
		final int currentHour = Calendar.getInstance().get(Calendar.HOUR_OF_DAY);

		// While there are still ads to schedule, we run the following loop.
		while(!notScheduled.isEmpty()) {
			final int adsNumber = notScheduled.size();

			int preferredHour = config.adsPreferredHour;
			if(currentHour >= preferredHour) {
				preferredHour = currentHour;

				// If there are only ten minutes left, we switch to the next hour.
				if(Calendar.getInstance().get(Calendar.MINUTE) >= 50) {
					preferredHour++;
				}
			}

			// The following loop is run once for hourMinus, once for hourPlus, then we substract one to hourMinus and add one to hourPlus (until we hit a limitation).
			boolean minus = true;
			for(int hourMinus = preferredHour, hourPlus = preferredHour; (hourMinus > currentHour || hourPlus <= 23) && !notScheduled.isEmpty(); minus = !minus) {
				// We evaluates how many ads we need to schedule for the current ad.
				final int hour = minus ? hourMinus : hourPlus;
				final int hourAdsNumber = Ad.getAdsPerHour(config, preferredHour, hour, adsNumber);

				// And we schedule them.
				for(int i = 0; i < hourAdsNumber; i++) {
					if(notScheduled.isEmpty()) {
						break;
					}

					final Ad ad = notScheduled.get(random.nextInt(notScheduled.size()));
					scheduled.put(hour, ad);
					notScheduled.remove(ad);
				}

				// We either increment or decrement the required field.
				if(minus) {
					hourMinus--;
				}
				else {
					hourPlus++;
				}

				// Then we check if we need to inverse the boolean.
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

	/**
	 * Checks if this instance has remaining ads to broadcast.
	 *
	 * @return Whether this instance has remaining ads to broadcast.
	 */

	public final boolean hasRemaining() {
		return !scheduled.isEmpty();
	}

	/**
	 * Broadcasts a random ad from the list of scheduled ads.
	 */

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

	/**
	 * Gets the time of the next ad broadcast.
	 * Or tomorrow midnight if there is no ad remaining.
	 *
	 * @return The time of the next ad broadcast.
	 */

	public final Calendar getNextSchedule() {
		if(scheduled.isEmpty()) {
			return Utils.tomorrowMidnight();
		}

		// We get the next ads schedule.
		int hour = Calendar.getInstance().get(Calendar.HOUR_OF_DAY);
		Collection<Ad> ads = null;
		for(; ads == null || ads.isEmpty(); hour++) {
			ads = scheduled.get(hour);
		}
		hour--;

		// Then we can schedule them.
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

		// Oh, and one last check (just to be sure).
		if(calendar.before(Calendar.getInstance())) {
			calendar = Calendar.getInstance();
		}

		return calendar;
	}

}