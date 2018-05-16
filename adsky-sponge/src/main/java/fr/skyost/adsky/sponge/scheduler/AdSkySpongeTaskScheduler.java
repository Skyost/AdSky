package fr.skyost.adsky.sponge.scheduler;

import fr.skyost.adsky.core.AdSkyTaskScheduler;
import fr.skyost.adsky.sponge.AdSkySpongePlugin;
import org.spongepowered.api.scheduler.Task;

import java.util.concurrent.TimeUnit;

/**
 * Default plugin scheduler.
 */

public class AdSkySpongeTaskScheduler implements AdSkyTaskScheduler {

	/**
	 * Plugin instance.
	 */

	private final AdSkySpongePlugin plugin;

	/**
	 * Creates a new scheduler instance.
	 *
	 * @param plugin A plugin instance.
	 */

	public AdSkySpongeTaskScheduler(final AdSkySpongePlugin plugin) {
		this.plugin = plugin;
	}

	@Override
	public final void schedule(final Runnable task, final long delay) {
		Task.builder().delay(delay, TimeUnit.MILLISECONDS).execute(task).submit(plugin);
	}

}