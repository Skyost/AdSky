package fr.skyost.adsky.core.scheduler;

import fr.skyost.adsky.core.AdSkyTaskScheduler;

import java.util.concurrent.Executors;
import java.util.concurrent.TimeUnit;

/**
 * Default AdSky task scheduler, using Executors.
 */

public class DefaultAdSkyTaskScheduler implements AdSkyTaskScheduler {

	@Override
	public void schedule(final Runnable task, final long delay) {
		Executors.newScheduledThreadPool(1).schedule(task, delay < 0 ? 0 : delay, TimeUnit.MILLISECONDS);
	}

}