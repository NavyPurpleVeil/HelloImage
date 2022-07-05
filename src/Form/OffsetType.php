<?php
namespace App\Form;

use App\Model\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class OffsetType extends AbstractType {
	public function buildform(FormBuilderInterface $builder, array $options) {
		$builder->add('offset', IntegerType::class);
	}
	
	public function configureOptions(OptionsResolver $resolver) {
	$resolver->setDefaults([
	 		'data_class' => Offset::class,
		]);
	}
}