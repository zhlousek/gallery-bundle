<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Bundle\GalleryBundle\Behat\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;
use Symfony\Component\HttpKernel\KernelInterface;

class WebUserContext extends PageObjectContext implements KernelAwareInterface
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * Sets Kernel instance.
     *
     * @param KernelInterface $kernel HttpKernel instance
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @Given /^bundle is configured to display (\d+) photos in gallery preview$/
     */
    public function bundleIsConfiguredToDisplayPhotosInGalleryPreview($photosCount)
    {
        expect($this->kernel
            ->getContainer()
            ->getParameter('fsi_gallery.preview_photos_count')
        )->toBe((int) $photosCount);
    }

    /**
     * @Given /^bundle is configured to display (\d+) galleries per page$/
     */
    public function bundleIsConfiguredToDisplayGalleriesPerPage($galleriesPerPage)
    {
        expect($this->kernel
                ->getContainer()
                ->getParameter('fsi_gallery.galleries_per_page')
        )->toBe((int) $galleriesPerPage);
    }

    /**
     * @When /^I open "([^"]*)" page$/
     * @Given /^I am on the "([^"]*)" page$/
     */
    public function iOpenPage($pageName)
    {
        $this->getPage($pageName)->open();
    }


    /**
     * @Given /^I am on the "([^"]*)" with name "([^"]*)" page$/
     */
    public function iAmOnTheWithNamePage($pageName, $name)
    {
        $gallery = $this->getMainContext()->getSubcontext('data')->findGalleryByName($name);
        $this->getPage($pageName)->open(array(
            'id' => $gallery->getId()
        ));
    }

    /**
     * @Then /^I should see following galleries$/
     */
    public function iShouldSeeFollowingGalleries(TableNode $galleries)
    {
        $page = $this->getPage('Galleries');
        expect($page->getGalleriesCount())->toBe(count($galleries));
        foreach ($galleries->getHash() as $galleryData) {
            expect($page->hasGallery($galleryData['Name']))->toBe(true);
            expect($page->getGalleryThumbnailsCount($galleryData['Name']))
                ->toBe((int) $galleryData['Photos count in preview']);
            expect($page->getGalleryDescription($galleryData['Name']))
                ->toBe($galleryData['Description']);
        }
    }

    /**
     * @Given /^each gallery thumbnail should have (\d+) px width and (\d+) px height$/
     */
    public function eachGalleryThumbnailShouldHavePxWidthAndPxHeight($width, $height)
    {
        $session = $this->getPage('Galleries')->getSession();
        $photoPreview = $this->getPage('Galleries')->getFirstGalleryPhotoThumbnail();
        $imgSrc = $photoPreview->getAttribute('src');
        $session->visit($imgSrc);
        $imgPath = $this->kernel->getRootDir() . '/../web' . $imgSrc;
        expect(file_exists($imgPath))->toBe(true);
        $size = getimagesize($imgPath);
        expect($size[0])->toBe((int) $width);
        expect($size[1])->toBe((int) $height);
    }

    /**
     * @Then /^I should see first (\d+) visible galleries at page$/
     */
    public function iShouldSeeFirstVisibleGalleriesAtPage($galleriesCount)
    {
        expect($this->getPage('Galleries')->getGalleriesCount())->toBe((int) $galleriesCount);
    }

    /**
     * @Given /^I should see pagination with following buttons$/
     */
    public function iShouldSeePaginationWithFollowingButtons(TableNode $buttons)
    {
        $page = $this->getPage('Galleries');
        expect($page->hasPagination())->toBe(true);

        foreach ($buttons->getHash() as $buttonData) {
            expect($page->hasPaginationButton($buttonData['Button']))->toBe(true);
            expect($page->isButtonDisabled($buttonData['Button']))
                ->toBe($buttonData['Disabled'] == 'true');
            expect($page->isButtonActive($buttonData['Button']))
                ->toBe($buttonData['Active'] == 'true');
        }
    }

    /**
     * @When /^I press "([^"]*)" header$/
     */
    public function iPressHeader($link)
    {
        $this->getPage('Galleries')->clickLink($link);
    }

    /**
     * @Then /^I should see header "([^"]*)"$/
     */
    public function iShouldSeeHeader($header)
    {
        expect($this->getPage('Gallery')->getHeader()->getText())->toBe($header);
    }

    /**
     * @Given /^I should see "([^"]*)" description$/
     */
    public function iShouldSeeDescription($description)
    {
        expect($this->getPage('Gallery')->getDescription()->getText())->toBe($description);
    }

    /**
     * @Given /^I should see (\d+) thumbnails that links to original photos$/
     */
    public function iShouldSeeThumbnailsThatLinksToOriginalPhotos($thumbnailsCount)
    {
        $thumbnails = $this->getPage('Gallery')->getThumbnails();
        expect(count($thumbnails))->toBe((int) $thumbnailsCount);
    }
}