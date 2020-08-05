<?php

namespace Drupal\Tests\acquia_cms\ExistingSiteJavascript;

use Drupal\Tests\acquia_cms_common\ExistingSiteJavascript\CohesionTestBase;

/**
 * Tests that Image Card Component.
 *
 * @group acquia_cms
 */
class CardComponentImage extends CohesionTestBase {

  /**
   * Test that Image card component is installed.
   *
   * And used in Cohesion's layout canvas.
   */
  public function testComponentInstalled() {
    $account = $this->createUser();
    $account->addRole('administrator');
    $account->save();
    $this->drupalLogin($account);

    $this->drupalGet('/node/add/page');
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();

    $page->fillField('Title', 'Cohesion card image component');

    // Add the cohesion component in the field layout canvas.
    $canvas = $this->waitForElementVisible('css', '.coh-layout-canvas');
    $component_added = $this->addComponent($canvas, 'Card - Image');
    $edit_form = $this->editComponent($component_added);
    $edit_form->pressButton('Select image');

    $this->addMedia('image');
    // Load the media library if it is configured.
    if ($this->assertSession()->waitForText('Media Library')) {
      // Upload media of type image.
      $this->uploadMediaInComponent();
    }

    $edit_form->pressButton('Apply');

    // Save the node and assign node object to the class property.
    $page = $this->waitForElementVisibleAssertion('css', '.form-actions');
    $page->pressButton('Save');
    $assert_session->pageTextContains('Page Cohesion card image component has been created.');
  }

  /**
   * Test that image card component is use/edit by site builder and developer.
   *
   * @dataProvider roleProvider
   */
  public function testComponentUseEditAccess($role) {
    $account = $this->createUser();
    $account->addRole($role);
    $account->save();
    $this->drupalLogin($account);

    // Visit to cohesion components page.
    $this->drupalGet('/admin/cohesion/components/components');
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();

    // Find general components category on page.
    $general_component = $page->find('css', '#edit-accordion');

    // Check weather general component accordion is open or not.
    if (!$general_component->hasAttribute('open')) {
      $this->waitForElementVisibleAssertion('css', '[aria-controls="edit-accordion"]')->press();
    }
    else {
      $this->waitForElementVisibleAssertion('css', '[aria-controls="edit-accordion"]');
    }

    // Click on 'edit' if the component exists on the page.
    $assert_session->pageTextContains('Card - Image');
    $this->getSession()->executeScript("jQuery('span:contains(Card - Image)').parents('tr:first').find('li.edit > a')[0].click()");
    $assert_session->waitForElement('css', '.cohesion-component-edit-form');

    // Save the component and check if the desired messages are present.
    $assert_session->buttonExists('Save and continue')->press();
    $assert_session->pageTextContains('Your component styles have been updated.');
    $assert_session->pageTextContains('Saved the Component Card - Image.');
  }

  /**
   * Provide roles.
   *
   * @return array
   *   Return role.
   */
  public function roleProvider() {
    return [
      ['site_builder'],
      ['developer'],
    ];
  }

}