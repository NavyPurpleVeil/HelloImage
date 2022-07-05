<?php
namespace App\Form;

use App\Model\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ImageType extends AbstractType {
	public function buildform(FormBuilderInterface $builder, array $options) {
		$builder->add('image', FileType::class, [
			'label' => 'Image',
			'mapped' => false,
			'required' => false,
			'constraints' => [
				new File([
					'maxSize' =>'1024k',
					'mimeTypes' => [
							'image/webp',
							'image/jpeg',
							'image/png',
							'image/bmp'
						],
						'mimeTypesMessage' => 'Please upload a valid IMAGE file',
				])
			],
		]);
	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults([
			'data_class' => Image::class,
		]);
	}
}