<?php

/**
 * @package     ContentBuilderNG
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

declare(strict_types=1);

\defined('_JEXEC') or die;
?>
<main class="container py-4 cb-cblist-syntax-help">
    <header class="mb-4">
        <h1 class="h2">
            <span class="fa-solid fa-circle-question me-2" aria-hidden="true"></span>
            <?php echo htmlspecialchars($this->pageTitle, ENT_QUOTES, 'UTF-8'); ?>
        </h1>
        <p class="lead mb-0">
            <?php echo htmlspecialchars($this->summary, ENT_QUOTES, 'UTF-8'); ?>
        </p>
    </header>

    <?php foreach ($this->sections as $section): ?>
        <section class="card mb-4">
            <div class="card-body">
                <h2 class="h4 card-title">
                    <?php echo htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8'); ?>
                </h2>
                <div class="card-text">
                    <?php echo $section['content']; ?>
                </div>
            </div>
        </section>
    <?php endforeach; ?>
</main>
